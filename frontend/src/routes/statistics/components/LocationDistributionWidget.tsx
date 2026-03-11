import React, { useEffect, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import { Button } from 'primereact/button';
import DbClient from '../../../utils/DbClient';

const COLORS = ['#0073e6', '#3399ff', '#66b2ff', '#99ccff', '#cce5ff'];

export const LocationDistributionWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [scale, setScale] = useState<'auto' | 'log'>('auto');

  useEffect(() => {
    DbClient.getLocationDistribution()
      .then(res => {
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data.map((d: any) => ({ ...d, total_count: Number(d.total_count) })));
        }
      })
      .catch(err => console.error(err))
      .finally(() => setLoading(false));
  }, []);

  const exportCSV = () => {
    const csv = ['Location,Quantity\n' + data.map(d => `"${d.location_name}",${d.total_count}`).join('\n')];
    const blob = new Blob(csv, { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'location_distribution.csv';
    link.click();
  };

  if (loading) return <div className="p-4">Loading location data...</div>;

  return (
    <div style={{ minWidth: 0, width: '100%', background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
      <div className="flex justify-content-between align-items-start mb-3">
        <h3 style={{ color: '#0053a1', margin: 0 }}>Top 10 Locations</h3>
        <div className="flex gap-2">
          <Button icon="pi pi-chart-bar" className={`p-button-sm p-button-rounded p-button-text ${scale === 'auto' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('auto')} tooltip="Escala Linear" tooltipOptions={{position: 'top'}} />
          <Button icon="pi pi-chart-line" className={`p-button-sm p-button-rounded p-button-text ${scale === 'log' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('log')} tooltip="Escala Logarítmica" tooltipOptions={{position: 'top'}} />
          <Button icon="pi pi-download" className="p-button-sm p-button-rounded p-button-text p-button-success" onClick={exportCSV} tooltip="Exportar CSV" tooltipOptions={{position: 'top'}} />
        </div>
      </div>

      {data.length === 0 ? (
        <div style={{ height: 300, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#888' }}>No data</div>
      ) : (
        <div style={{ width: '100%', height: 300, position: 'relative' }}>
          <div style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}>
            <ResponsiveContainer width="100%" height="100%">
              <BarChart layout="vertical" data={data} margin={{ top: 10, right: 30, left: 40, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" horizontal={true} vertical={false} />
                <XAxis type="number" scale={scale} domain={scale === 'log' ? [1, 'auto'] : [0, 'auto']} allowDataOverflow />
                <YAxis dataKey="location_name" type="category" width={80} tick={{fontSize: 12}} />
                <Tooltip cursor={{fill: '#f5f5f5'}} />
                <Bar dataKey="total_count" name="Quantity" radius={[0, 4, 4, 0]}>
                  {data.map((_entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
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