import { useState, useRef } from 'react';
import { AutoComplete, AutoCompleteCompleteEvent } from 'primereact/autocomplete';
import { useNavigate } from 'react-router-dom';
import { defaultQueryFunction } from '../../utils/DbClient';
import { ROUTES } from '../../utils/routes';

interface SearchResult {
    type: string;
    label: string;
    description: string;
    icon: string;
    url: string;
}

export default function GlobalSearch() {
    const [query, setQuery] = useState<string>('');
    const [items, setItems] = useState<SearchResult[]>([]);
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    const timeoutRef = useRef<any>(null);

    const search = (event: AutoCompleteCompleteEvent) => {
        const q = event.query;
        if (timeoutRef.current) clearTimeout(timeoutRef.current);

        if (q.trim().length < 2) {
            setItems([]);
            return;
        }

        timeoutRef.current = setTimeout(async () => {
            setLoading(true);
            try {
                const res = await defaultQueryFunction({
                    queryKey: ['search', 'get', { q: q }]
                });
                setItems(res.data || []);
            } catch (error) {
                console.error("Search error", error);
                setItems([]);
            } finally {
                setLoading(false);
            }
        }, 300);
    };

    const onSelect = (e: any) => {
        const item = e.value as SearchResult;
        setQuery(''); 
        navigate(item.url); 
    };

    const onKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter' && query.trim().length > 0) {
            navigate(`${ROUTES.SEARCH}?q=${encodeURIComponent(query)}`);
            setQuery(''); 
            setItems([]); 
        }
    };

    const itemTemplate = (item: SearchResult) => {
        return (
            <div className="flex align-items-center gap-3 p-2 border-bottom-1 surface-border hover:surface-hover transition-colors">
                <div className={`flex align-items-center justify-content-center border-circle w-2rem h-2rem bg-blue-100`}>
                    <i className={`pi ${item.icon} text-blue-600 text-lg`}></i>
                </div>
                <div className="flex flex-column">
                    <span className="font-bold text-700">{item.label}</span>
                    <span className="text-sm text-500">{item.description}</span>
                </div>
            </div>
        );
    };

    return (
        <div className="flex align-items-center bg-white border-round-3xl px-2 py-1 shadow-1" style={{ width: '300px', border: '1px solid #e2e8f0' }}>
            <i className="pi pi-search text-500 mr-2 ml-1"></i>
            <AutoComplete 
                value={query} 
                suggestions={items} 
                completeMethod={search} 
                onChange={(e) => setQuery(e.value)} 
                onSelect={onSelect}
                onKeyDown={onKeyDown}
                itemTemplate={itemTemplate}
                placeholder="Search IDs, Locations, Status..." 
                className="w-full"
                inputClassName="w-full border-none shadow-none text-600"
                panelClassName="shadow-4 border-round-xl"
                emptyMessage="No results found"
                autoHighlight
            />
            {loading && <i className="pi pi-spin pi-spinner text-blue-500 ml-2"></i>}
        </div>
    );
}