import React, { useEffect, useState } from 'react';
import { PieChart, Pie, Tooltip, ResponsiveContainer, Cell, Legend } from 'recharts';
import { Button } from 'primereact/button';
import { SelectButton } from 'primereact/selectbutton';
import DbClient from '../../../utils/DbClient';
import { exportChartAsImage } from '../../../utils/exportUtils';

const COLORS = ['#0053a1', '#3399ff', '#ff9900', '#33cc33', '#ff6666', '#99ccff', '#cce5ff'];

export const OwnerDistributionWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [exportFormat, setExportFormat] = useState<'PNG' | 'PDF' | 'CSV'>('PNG');
  const exportOptions = ['PNG', 'PDF', 'CSV'];

  useEffect(() => {
    DbClient.getOwnerDistribution()
      .then(res => {
        const responseData = res?.data ? res.data : (Array.isArray(res) ? res : []);

        if (Array.isArray(responseData)) {
          setData(responseData.map((d: any) => ({
            ...d,
            owner_name: d.owner_name || d.name || 'Unknown',
            total_count: Number(d.total_count || d.count || 0)
          })));
        }
      })
      .catch(err => console.error("Error fetching Owner Distribution:", err))
      .finally(() => setLoading(false));
  }, []);

  const handleExport = () => {
    if (exportFormat === 'CSV') {
        const csvString = 'Owner,Quantity\n' + data.map(d => `"${d.owner_name}",${d.total_count}`).join('\n');
        
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'owner_distribution.csv';
        link.click();
    } else {
        exportChartAsImage('owner-chart', 'owner_distribution', exportFormat);
    }
  };

  if (loading) return <div className="p-4">Loading owner data...</div>;

  return (
    <div style={{ minWidth: 0, width: '100%', background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
      <div className="flex justify-content-between align-items-start mb-3">
        <h3 style={{ color: '#0053a1', margin: 0 }}>Distribution by Owner</h3>
        <div className="flex align-items-center gap-2">
          <SelectButton value={exportFormat} options={exportOptions} onChange={(e) => { if(e.value) setExportFormat(e.value) }} className="p-button-sm ml-3" style={{transform: 'scale(0.85)', transformOrigin: 'right'}} />
          <Button icon="pi pi-download" className="p-button-rounded p-button-success" onClick={handleExport} tooltip={`Export as ${exportFormat}`} tooltipOptions={{position: 'top'}} />
        </div>
      </div>

      {data.length === 0 ? (
        <div style={{ height: 350, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#888' }}>No data</div>
      ) : (
        <div style={{ width: '100%', height: 350, position: 'relative' }}>
          <div id="owner-chart" style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}>
            <ResponsiveContainer width="100%" height="100%">
              <PieChart margin={{ top: 10, right: 30, left: 20, bottom: 20 }}>
                <Pie
                  data={data}
                  dataKey="total_count"
                  nameKey="owner_name"
                  cx="50%"
                  cy="50%"
                  outerRadius={100}
                  label={(props: any) => `${props.name} (${((props.percent || 0) * 100).toFixed(1)}%)`}
                >
                  {data.map((_entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip formatter={(value) => [value, 'Quantity']} />
                <Legend layout="horizontal" verticalAlign="bottom" align="center" wrapperStyle={{ paddingTop: '20px' }} />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}
    </div>
  );
};