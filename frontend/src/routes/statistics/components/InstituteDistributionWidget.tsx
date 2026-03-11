import React, { useEffect, useState } from 'react';
import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer, Legend } from 'recharts';
import { Button } from 'primereact/button';
import DbClient from '../../../utils/DbClient';

const INSTITUTE_COLORS = ['#0053a1', '#e85c0f', '#008000', '#f4b400', '#6050dc', '#dc3545', '#17a2b8', '#6c757d'];

export const InstituteDistributionWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    DbClient.getInstituteDistribution()
      .then(res => {
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data.map((d: any) => ({ ...d, total_count: Number(d.total_count) })));
        }
      })
      .catch(err => console.error(err))
      .finally(() => setLoading(false));
  }, []);

  const exportCSV = () => {
    const csv = ['Institute,Quantity\n' + data.map(d => `"${d.institute_name}",${d.total_count}`).join('\n')];
    const blob = new Blob(csv, { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'institute_distribution.csv';
    link.click();
  };

  const renderLegend = (value: string, entry: any) => {
    return <span style={{ color: '#333', fontWeight: 500, marginRight: '10px' }}>{value} ({entry.payload.total_count})</span>;
  };

  if (loading) return <div className="p-4">Loading institute data...</div>;

  return (
    <div style={{ minWidth: 0, width: '100%', background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
      <div className="flex justify-content-between align-items-start mb-3">
        <h3 style={{ color: '#0053a1', margin: 0 }}>Distribution by Institute</h3>
        <Button icon="pi pi-download" className="p-button-sm p-button-rounded p-button-text p-button-success" onClick={exportCSV} tooltip="Exportar CSV" tooltipOptions={{position: 'top'}} />
      </div>

      {data.length === 0 ? (
        <div style={{ height: 300, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#888' }}>No data</div>
      ) : (
        <div style={{ width: '100%', height: 300, position: 'relative' }}>
          <div style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}>
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={data} dataKey="total_count" nameKey="institute_name" cx="50%" cy="50%" innerRadius={70} outerRadius={90} paddingAngle={2}>
                  {data.map((_entry, index) => (
                    <Cell key={`cell-${index}`} fill={INSTITUTE_COLORS[index % INSTITUTE_COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip />
                <Legend formatter={renderLegend} verticalAlign="bottom" height={40}/>
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}
    </div>
  );
};