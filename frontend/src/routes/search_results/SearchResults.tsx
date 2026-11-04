import { Fragment } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { defaultQueryFunction } from '../../utils/DbClient';
import SearchControls from '../../components/search_controls/SearchControls';

const categories = [['equipment','Equipment'],['location','Locations'],['detector','Detector locations'],['status','Statuses'],['institute','Institutes']];

// React escapes every fragment; database text is never interpreted as HTML.
export function Highlight({text, query, mode}: {text: string; query: string; mode: string}) {
  const terms = (mode === 'all_words' ? query.split(/\s+/u) : [query]).filter(Boolean).sort((a,b)=>b.length-a.length);
  if (!terms.length) return <>{text}</>;
  const escaped = terms.map(term=>term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
  const pattern = escaped.join('|');
  const expression = new RegExp(mode === 'word' ? `((?<![\\p{L}\\p{N}_])(?:${pattern})(?![\\p{L}\\p{N}_]))` : '(' + pattern + ')', 'giu');
  return <>{text.split(expression).map((part,index)=>index % 2 ? <mark key={index}>{part}</mark> : <Fragment key={index}>{part}</Fragment>)}</>;
}

export default function SearchResults() {
  const [params, setParams] = useSearchParams();
  const query = params.get('q') || '';
  const mode = params.get('mode') || 'contains';
  const category = params.get('category') || 'equipment';
  const request = Object.fromEntries(params.entries());
  const searchQuery: any = useQuery({queryKey:['search','get',request],queryFn:defaultQueryFunction,enabled:!!query.trim(),retry:false});
  const data = searchQuery.data?.data;
  const results = data?.results || [];
  const change = (key:string,value:string)=>{const next=new URLSearchParams(params);next.set(key,value);if(key!=='page')next.delete('page');setParams(next);};
  return <main className="p-4 flex flex-column gap-3" style={{maxWidth:'1200px',margin:'0 auto'}}>
    <h1 className="m-0">Search</h1>
    <SearchControls params={params} onSearch={setParams}/>
    {!query.trim() ? <p>Enter text and choose the fields to search.</p> : <>
      <div className="flex flex-wrap gap-2" aria-label="Result categories">
        {categories.map(([value,label])=><button key={value} type="button" aria-pressed={category===value}
          className={`p-button p-button-sm ${category===value?'':'p-button-outlined'}`} onClick={()=>change('category',value)}>
          {label}{data ? ` (${data.counts[value]})` : ''}
        </button>)}
      </div>
      <div className="flex flex-wrap align-items-center justify-content-between gap-2">
        <span aria-live="polite">{data && !searchQuery.isFetching ? `${data.total} results for “${query}”` : 'Searching…'}</span>
        <label>Sort by <select className="p-inputtext ml-2" value={params.get('sort') || 'relevance'} onChange={e=>change('sort',e.target.value)}>
          <option value="relevance">Relevance</option><option value="id">ID / Name</option><option value="updated">Recently updated</option>
        </select></label>
      </div>
      {searchQuery.isFetching && <p role="status"><i className="pi pi-spin pi-spinner mr-2"/>Loading results…</p>}
      {searchQuery.isError && <div role="alert" className="p-message p-message-error p-3">
        {searchQuery.error?.response?.data?.message || 'Search failed. Please retry.'}
        <button className="p-button p-button-text ml-2" onClick={()=>searchQuery.refetch()}>Retry</button>
      </div>}
      {!searchQuery.isFetching && !searchQuery.isError && data && <>
        {!results.length && <p>No matches in this category and the selected fields. Try another category or adjust the search.</p>}
        {results.map((item:any)=><article key={`${item.type}:${item.label}`} className="surface-card border-1 surface-border border-round p-3">
          <div className="flex flex-wrap gap-2 align-items-baseline"><Link className="font-bold text-xl" to={item.url}>{item.label}</Link>
            <span className="text-700">{item.description}</span></div>
          {item.type==='equipment' && <p className="text-sm text-600 my-2">
            {[item.serial && `Serial: ${item.serial}`,item.location && `Location: ${item.location}`,item.detector_location && `Detector: ${item.detector_location}`,item.status && `Status: ${item.status}`].filter(Boolean).join(' · ')}
          </p>}
          <dl className="m-0 mt-2">{item.matches.map((match:any)=><div key={match.field} className="mb-2" style={{overflowWrap:'anywhere'}}>
            <dt className="font-bold inline mr-2">{match.field}:</dt><dd className="inline m-0"><Highlight text={match.text} query={query} mode={mode}/></dd>
          </div>)}</dl>
        </article>)}
        {data.total>0 && <nav className="flex align-items-center justify-content-center gap-3" aria-label="Search pagination">
          <button className="p-button p-button-outlined" disabled={data.page<=1} onClick={()=>change('page',String(data.page-1))}>Previous</button>
          <span>Page {data.page} of {Math.ceil(data.total/data.page_size)}</span>
          <button className="p-button p-button-outlined" disabled={data.page*data.page_size>=data.total} onClick={()=>change('page',String(data.page+1))}>Next</button>
        </nav>}
      </>}
    </>}
  </main>;
}
