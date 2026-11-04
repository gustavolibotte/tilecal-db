import { useEffect, useState } from 'react';

export const searchFields = [
  ['identifiers', 'ID / Serial'], ['type', 'Equipment type'], ['location', 'Location / Detector'],
  ['status', 'Status'], ['owner', 'Owner'], ['comments', 'Comments']
];
export const searchModes = [
  ['contains', 'Contains text'], ['all_words', 'All words'], ['word', 'Whole word / phrase'],
  ['prefix', 'Starts with'], ['exact', 'Exact field value']
];
const help: Record<string, string> = {
  contains: 'Find this text anywhere within a selected field.',
  all_words: 'Find every word, in any order, across the selected fields (up to 10 words).',
  word: 'Match a complete word or phrase, not part of a larger word.',
  prefix: 'The selected field must start with this text.',
  exact: 'The entire field must equal this text.'
};
export default function SearchControls({params, onSearch}: {params: URLSearchParams; onSearch: (next: URLSearchParams)=>void}) {
  const [q, setQ] = useState(params.get('q') || '');
  const [mode, setMode] = useState(params.get('mode') || 'contains');
  const [fields, setFields] = useState((params.get('fields') ?? searchFields.map(f=>f[0]).join(',')).split(','));
  const signature = params.toString();
  useEffect(()=>{
    setQ(params.get('q') || ''); setMode(params.get('mode') || 'contains');
    setFields((params.get('fields') ?? searchFields.map(f=>f[0]).join(',')).split(','));
  }, [signature]);
  return <form onSubmit={event=>{event.preventDefault(); if (!q.trim() || !fields.length) return;
    onSearch(new URLSearchParams({q: q.trim(), mode, fields: fields.join(','), category:'equipment', sort:params.get('sort') || 'relevance'}));
  }} className="surface-card border-1 surface-border border-round p-3">
    <div className="flex flex-wrap align-items-end gap-3">
      <label className="flex flex-column gap-2 flex-1" htmlFor="search-text">Search text
        <input id="search-text" className="p-inputtext w-full" value={q} maxLength={200} onChange={e=>setQ(e.target.value)} placeholder="ID, comment, location…" />
      </label>
      <label className="flex flex-column gap-2" htmlFor="search-mode">Match
        <select id="search-mode" className="p-inputtext" value={mode} onChange={e=>setMode(e.target.value)}>
          {searchModes.map(([value,label])=><option key={value} value={value}>{label}</option>)}
        </select>
      </label>
      <button className="p-button" type="submit" disabled={!q.trim() || !fields.length}>Search</button>
    </div>
    <p className="text-sm text-600">{help[mode]} Case-insensitive; accents and punctuation are preserved.</p>
    <fieldset className="border-none p-0 m-0"><legend className="mb-2 font-bold">Search in</legend>
      <div className="flex flex-wrap gap-3">{searchFields.map(([value,label])=><label key={value} className="flex align-items-center gap-2">
        <input type="checkbox" checked={fields.includes(value)} onChange={e=>setFields(previous=>e.target.checked ? [...previous,value] : previous.filter(f=>f!==value))} />{label}
      </label>)}</div>
      <div className="flex gap-2 mt-3">
        <button type="button" className="p-button p-button-text p-button-sm" onClick={()=>setFields(['comments'])}>Comments only</button>
        <button type="button" className="p-button p-button-text p-button-sm" onClick={()=>setFields(searchFields.map(f=>f[0]).filter(f=>f!=='comments'))}>Other fields</button>
        <button type="button" className="p-button p-button-text p-button-sm" onClick={()=>setFields(searchFields.map(f=>f[0]))}>All fields</button>
      </div>
    </fieldset>
    {!fields.length && <p role="alert">Select at least one field.</p>}
  </form>;
}
