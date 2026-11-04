import { useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { defaultQueryFunction } from '../../utils/DbClient';
import { ROUTES } from '../../utils/routes';

export default function GlobalSearch() {
  const [query,setQuery]=useState('');
  const [items,setItems]=useState<any[]>([]);
  const [open,setOpen]=useState(false);
  const [loading,setLoading]=useState(false);
  const [failed,setFailed]=useState(false);
  const generation=useRef(0);
  const host=useRef<HTMLDivElement>(null);
  const navigate=useNavigate();
  const url=`${ROUTES.SEARCH}?${new URLSearchParams({q:query.trim()})}`;
  useEffect(()=>{
    const version=++generation.current;setItems([]);setFailed(false);setLoading(false);
    if(query.trim().length<2)return;
    setLoading(true);
    const timer=setTimeout(async()=>{
      try {
        const response:any=await defaultQueryFunction({queryKey:['search','get',{q:query.trim(),preview:1}]});
        if(version===generation.current)setItems(response.data?.results?.slice(0,6)||[]);
      }catch{if(version===generation.current)setFailed(true);}
      finally{if(version===generation.current)setLoading(false);}
    },350);
    return ()=>{clearTimeout(timer);generation.current++;};
  },[query]);
  useEffect(()=>{
    const close=(event:MouseEvent)=>{if(!host.current?.contains(event.target as Node))setOpen(false);};
    document.addEventListener('mousedown',close);return ()=>document.removeEventListener('mousedown',close);
  },[]);
  return <div ref={host} className="relative" style={{width:'320px',maxWidth:'100%'}} onBlur={e=>{if(!e.currentTarget.contains(e.relatedTarget as Node))setOpen(false);}}>
    <form className="flex align-items-center surface-card border-1 surface-border border-round-3xl p-1" onSubmit={e=>{e.preventDefault();setOpen(false);navigate(url);}}>
      <input aria-label="Global search" className="p-inputtext border-none shadow-none" style={{width:'100%',minWidth:0}} value={query} maxLength={200}
        placeholder="Search IDs, comments…" onFocus={()=>setOpen(true)} onChange={e=>{setQuery(e.target.value);setOpen(true);}}
        onKeyDown={e=>{if(e.key==='Escape')setOpen(false);}}/>
      <button type="submit" className="p-button p-button-text p-button-rounded" aria-label="Open search and filters" title="Search and filters"><i className="pi pi-search"/></button>
    </form>
    {open && query.trim().length>=2 && <div className="absolute right-0 surface-card shadow-4 border-round p-3 z-5" style={{width:'min(440px, 90vw)'}}>
      <p className="text-sm text-600 mt-0">Equipment suggestions · All fields, including comments</p>
      {loading ? <p role="status">Searching…</p> : failed ? <p role="alert">Could not load suggestions. Open search to retry.</p> :
        items.length ? <ul className="list-none p-0 m-0">{items.map(item=><li key={item.label} className="mb-3">
          <Link to={item.url} onClick={()=>setOpen(false)} className="font-bold">{item.label}</Link> <span>{item.description}</span>
          {item.matches?.[0] && <div className="text-sm text-600" style={{overflowWrap:'anywhere'}}>{item.matches[0].field}: {item.matches[0].text}</div>}
        </li>)}</ul> : <p>No matching equipment.</p>}
      <Link to={url} onClick={()=>setOpen(false)} className="font-bold">View all results and search filters →</Link>
    </div>}
  </div>;
}
