import React, { useEffect, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import { Button } from 'primereact/button';
import { SelectButton } from 'primereact/selectbutton';
import DbClient from '../../../utils/DbClient';
import { exportChartAsImage } from '../../../utils/exportUtils';

const ALERT_COLORS = ['#e85c0f', '#f47b20', '#ff9933', '#ffb366', '#ffcc99'];

export const OrphanedComponentsWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [scale, setScale] = useState<'auto' | 'log'>('auto');
  const [exportFormat, setExportFormat] = useState<'PNG' | 'PDF' | 'CSV'>('PNG');
  const exportOptions = ['PNG', 'PDF', 'CSV'];

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

  const handleExport = () => {
    if (exportFormat === 'CSV') {
      const csv = ['Equipment Type,Quantity\n' + data.map(d => `"${d.type_name}",${d.total_count}`).join('\n')];
      const blob = new Blob(csv, { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'orphaned_components.csv';
      link.click();
    } else {
      exportChartAsImage('orphaned-chart', 'orphaned_components', exportFormat);
    }
  };

  if (loading) return <div className="p-4">Loading orphaned components...</div>;

  return (
    <div style={{ minWidth: 0, width: '100%', background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)', borderTop: '4px solid #e85c0f' }}>
      <div className="flex justify-content-between align-items-start mb-3">
        <div>
          <h3 style={{ color: '#e85c0f', marginTop: 0, marginBottom: '4px' }}>Ready for Assembly</h3>
          <span style={{ fontSize: '0.85rem', color: '#666' }}>Tested or Stored components currently available for assembly.</span>
        </div>
        <div className="flex align-items-center gap-2">
          <Button icon="pi pi-chart-bar" className={`p-button-sm p-button-rounded p-button-text ${scale === 'auto' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('auto')} tooltip="Linear Scale" tooltipOptions={{position: 'top'}} />
          <Button icon="pi pi-chart-line" className={`p-button-sm p-button-rounded p-button-text ${scale === 'log' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('log')} tooltip="Logarithmic Scale" tooltipOptions={{position: 'top'}} />
          
          <SelectButton value={exportFormat} options={exportOptions} onChange={(e) => { if(e.value) setExportFormat(e.value) }} className="p-button-sm ml-3" style={{transform: 'scale(0.85)', transformOrigin: 'right'}} />
          <Button icon="pi pi-download" className="p-button-rounded p-button-success" onClick={handleExport} tooltip={`Export as ${exportFormat}`} tooltipOptions={{position: 'top'}} />
        </div>
      </div>
      
      {data.length === 0 ? (
        <div style={{ height: 350, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#888' }}>
          No components ready for assembly found.
        </div>
      ) : (
        <div style={{ width: '100%', height: 400, position: 'relative' }}> 
          <ResponsiveContainer width="100%" height="100%">
            <BarChart 
              data={data} 
              margin={{ top: 20, right: 30, left: 20, bottom: 100 }}
            >
              <CartesianGrid strokeDasharray="3 3" vertical={false} />
              <XAxis 
                dataKey="type_name" 
                tick={{fontSize: 10}} 
                interval={0} 
                angle={-45} 
                textAnchor="end" 
                height={100} 
              />
              <YAxis 
                domain={[0, 'auto']} 
                label={{ value: 'Quantity', angle: -90, position: 'insideLeft', offset: 0 }}
              />
              <Tooltip cursor={{fill: '#fff3e6'}} formatter={(value) => [value, 'Quantity']} />
              <Bar dataKey="total_count" name="Quantity" radius={[4, 4, 0, 0]}>
                {data.map((_entry, index) => (
                  <Cell key={`cell-${index}`} fill={ALERT_COLORS[index % ALERT_COLORS.length]} />
                ))}
              </Bar>
            </BarChart>
          </ResponsiveContainer>
        </div>
      )}
    </div>
  );
};