import { useSearchParams, useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { defaultQueryFunction } from '../../utils/DbClient';
import { Card } from 'primereact/card';
import { Button } from 'primereact/button';

export default function SearchResults() {
    const [searchParams] = useSearchParams();
    const query = searchParams.get('q') || '';
    const navigate = useNavigate();

    const searchQuery: any = useQuery({
        queryKey: ['search', 'get', { q: query }],
        queryFn: defaultQueryFunction,
        enabled: query.length > 1
    });

    if (searchQuery.isPending && query.length > 1) {
        return <div className="p-4 text-center"><i className="pi pi-spin pi-spinner text-4xl text-blue-500"></i></div>;
    }

    const results = searchQuery.data?.data || [];

    if (!results || results.length === 0) {
        return (
            <div className="p-6 text-center">
                <i className="pi pi-search text-6xl text-300 mb-4"></i>
                <h2>No results found for "{query}"</h2>
                <p className="text-500">Try adjusting your search terms or browsing via the main menus.</p>
            </div>
        );
    }

    const grouped = results.reduce((acc: any, curr: any) => {
        if (!acc[curr.type]) acc[curr.type] = [];
        acc[curr.type].push(curr);
        return acc;
    }, {});

    return (
        <div className="flex flex-column gap-4 p-4">
            <div className="border-bottom-1 surface-border pb-3">
                <h2 className="m-0 text-800">Search Results</h2>
                <p className="text-500 m-0 mt-1">Showing best matches for <b>"{query}"</b></p>
            </div>

            <div className="grid">
                {Object.keys(grouped).map(type => (
                    <div key={type} className="col-12 md:col-6 lg:col-4">
                        <Card title={<span className="capitalize text-blue-700">{type}s</span>} className="shadow-2 h-full">
                            <div className="flex flex-column gap-3">
                                {grouped[type].map((item: any, idx: number) => (
                                    <div key={idx} className="flex align-items-center justify-content-between p-2 border-round surface-50 hover:surface-100 transition-colors cursor-pointer" onClick={() => navigate(item.url)}>
                                        <div className="flex align-items-center gap-3">
                                            <i className={`pi ${item.icon} text-blue-500 text-xl`}></i>
                                            <div className="flex flex-column">
                                                <span className="font-bold text-700">{item.label}</span>
                                                <span className="text-sm text-500">{item.description}</span>
                                            </div>
                                        </div>
                                        <Button icon="pi pi-chevron-right" text rounded />
                                    </div>
                                ))}
                            </div>
                        </Card>
                    </div>
                ))}
            </div>
        </div>
    );
}