import React, { useEffect, useState } from 'react';
import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer, Legend, Sector } from 'recharts';
import { Button } from 'primereact/button';
import { SelectButton } from 'primereact/selectbutton';
import DbClient from '../../../utils/DbClient';
import { exportChartAsImage } from '../../../utils/exportUtils';

const INSTITUTE_COLORS = ['#0053a1', '#e85c0f', '#008000', '#f4b400', '#6050dc', '#dc3545', '#17a2b8', '#6c757d'];

const PieGradient = (props: any) => {
  const { cx, cy, outerRadius, index, fill, isActive } = props; 
  return (
    <>
      <defs>
        <radialGradient id={`fillGradIns${index}`} cx={cx} cy={cy} r={outerRadius} gradientUnits="userSpaceOnUse">
          <stop offset="0%" stopColor={fill} stopOpacity={0.1} />
          <stop offset="100%" stopColor={fill} stopOpacity={0.8} />
        </radialGradient>
        <clipPath id={`clipIns${index}`}><Sector {...props} /></clipPath>
      </defs>
      <Sector {...props} clipPath={`url(#clipIns${index})`} fill={`url(#fillGradIns${index})`} stroke={fill} strokeWidth={isActive ? 2 : 1} />
    </>
  );
};

export const InstituteDistributionWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [exportFormat, setExportFormat] = useState<'PNG' | 'PDF' | 'CSV'>('PNG');
  const exportOptions = ['PNG', 'PDF', 'CSV'];

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

  const handleExport = () => {
    if (exportFormat === 'CSV') {
      const csv = ['Institute,Quantity\n' + data.map(d => `"${d.institute_name}",${d.total_count}`).join('\n')];
      const blob = new Blob(csv, { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'institute_distribution.csv';
      link.click();
    } else {
      exportChartAsImage('institute-chart', 'institute_distribution', exportFormat);
    }
  };

  const renderLegend = (value: string, entry: any) => (
    <span title={`Institute: ${value} | Quantity: ${entry.payload.total_count}`} style={{ color: '#333', fontWeight: 500, marginRight: '10px', cursor: 'help' }}>
      {value} ({entry.payload.total_count})
    </span>
  );

  if (loading) return <div className="p-4">Loading institute data...</div>;

  return (
    <div style={{ minWidth: 0, width: '100%', background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
      <div className="flex justify-content-between align-items-start mb-3">
        <h3 style={{ color: '#0053a1', margin: 0 }}>Distribution by Institute</h3>
        <div className="flex align-items-center gap-2">
          <SelectButton value={exportFormat} options={exportOptions} onChange={(e) => { if(e.value) setExportFormat(e.value) }} className="p-button-sm ml-3" style={{transform: 'scale(0.85)', transformOrigin: 'right'}} />
          <Button icon="pi pi-download" className="p-button-rounded p-button-success" onClick={handleExport} tooltip={`Export as ${exportFormat}`} tooltipOptions={{position: 'top'}} />
        </div>
      </div>

      {data.length === 0 ? (
        <div style={{ height: 350, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#888' }}>No data</div>
      ) : (
        <div style={{ width: '100%', height: 350, position: 'relative' }}>
          <div id="institute-chart" style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}>
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={data} dataKey="total_count" nameKey="institute_name" cx="50%" cy="50%" innerRadius="25%" outerRadius="75%" paddingAngle={2} shape={PieGradient}>
                  {data.map((_entry, index) => (
                    <Cell key={`cell-${index}`} fill={INSTITUTE_COLORS[index % INSTITUTE_COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip formatter={(value, name) => [value, name]} />
                <Legend formatter={renderLegend} verticalAlign="bottom" height={40}/>
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}
    </div>
  );
};