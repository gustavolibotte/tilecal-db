import React, { useEffect, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import { Button } from 'primereact/button';
import DbClient from '../../../utils/DbClient';

const ALERT_COLORS = ['#e85c0f', '#f47b20', '#ff9933', '#ffb366', '#ffcc99'];

export const OrphanedComponentsWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [scale, setScale] = useState<'auto' | 'log'>('auto');

  useEffect(() => {
    DbClient.getOrphanedComponents()
      .then(res => {
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data.map((d: any) => ({ ...d, total_count: Number(d.total_count) })));
        }
      })
      .catch(err => console.error(err))
      .finally(() => setLoading(false));
  }, []);

  const exportCSV = () => {
    const csv = ['Equipment Type,Quantity\n' + data.map(d => `"${d.type_name}",${d.total_count}`).join('\n')];
    const blob = new Blob(csv, { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'orphaned_components.csv';
    link.click();
  };

  if (loading) return <div className="p-4">Loading orphaned components...</div>;

  return (
    <div style={{ minWidth: 0, width: '100%', background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)', borderTop: '4px solid #e85c0f' }}>
      <div className="flex justify-content-between align-items-start mb-3">
        <div>
          <h3 style={{ color: '#e85c0f', marginTop: 0, marginBottom: '4px' }}>Orphaned Components (Bottlenecks)</h3>
          <span style={{ fontSize: '0.85rem', color: '#666' }}>Components with 'Good' status not yet assembled.</span>
        </div>
        <div className="flex gap-2">
          <Button icon="pi pi-chart-bar" className={`p-button-sm p-button-rounded p-button-text ${scale === 'auto' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('auto')} tooltip="Linear Scale" tooltipOptions={{position: 'top'}} />
          <Button icon="pi pi-chart-line" className={`p-button-sm p-button-rounded p-button-text ${scale === 'log' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('log')} tooltip="Log Scale" tooltipOptions={{position: 'top'}} />
          <Button icon="pi pi-download" className="p-button-sm p-button-rounded p-button-text p-button-success" onClick={exportCSV} tooltip="Export CSV" tooltipOptions={{position: 'top'}} />
        </div>
      </div>
      
      {data.length === 0 ? (
        <div style={{ height: 350, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#888' }}>No bottlenecks found.</div>
      ) : (
        <div style={{ width: '100%', height: 350, position: 'relative' }}>
          <div style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}>
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={data} margin={{ top: 10, right: 30, left: 0, bottom: 90 }}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="type_name" tick={{fontSize: 11}} interval={0} angle={-45} textAnchor="end" />
                <YAxis scale={scale} domain={scale === 'log' ? [1, 'auto'] : [0, 'auto']} allowDataOverflow />
                <Tooltip cursor={{fill: '#fff3e6'}} />
                <Bar dataKey="total_count" name="Quantity" radius={[4, 4, 0, 0]}>
                  {data.map((_entry, index) => (
                    <Cell key={`cell-${index}`} fill={ALERT_COLORS[index % ALERT_COLORS.length]} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}
    </div>
  );
};