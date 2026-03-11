import React, { useEffect, useState } from 'react';
import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer, Legend, Sector } from 'recharts';
import { Button } from 'primereact/button';
import DbClient from '../../../utils/DbClient';

const STATUS_COLORS: { [key: string]: string } = {
  'Good': '#008000',
  'Broken': '#dc3545',
  'Under Test': '#0053a1',
  'To Be Repaired': '#ffc107',
  'Lost': '#6c757d',
  'In Use': '#17a2b8',
  'Scrapped': '#343a40',
  'Returned': '#e83e8c',
  'Maintenance': '#fd7e14'
};
const FALLBACK_COLORS = ['#8884d8', '#82ca9d', '#ffc658', '#d0ed57', '#a4de6c', '#ff8042', '#00C49F'];

const StatusPieGradient = (props: any) => {
  const { cx, cy, outerRadius, index, fill, width, height, isActive } = props;
  return (
    <>
      <defs>
        <radialGradient id={`fillGradStatus${index}`} cx={cx} cy={cy} r={outerRadius} gradientUnits="userSpaceOnUse">
          <stop offset="0%" stopColor={fill} stopOpacity={0} />
          <stop offset="100%" stopColor={fill} stopOpacity={0.8} />
        </radialGradient>
        <radialGradient id={`borderGradStatus${index}`} cx={(typeof width === 'number' ? width : 0) / 2} cy={(typeof height === 'number' ? height : 0) / 2} r={outerRadius} gradientUnits="userSpaceOnUse">
          <stop offset="0%" stopColor={fill} stopOpacity={0} />
          <stop offset="100%" stopColor={fill} stopOpacity={0.8} />
        </radialGradient>
        <clipPath id={`clipStatus${index}`}>
          <Sector {...props} />
        </clipPath>
      </defs>
      <Sector
        {...props}
        clipPath={`url(#clipStatus${index})`}
        fill={`url(#fillGradStatus${index})`}
        stroke={`url(#borderGradStatus${index})`}
        strokeWidth={isActive ? '100%' : 0}
      />
    </>
  );
};

export const StatusDistributionWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    DbClient.getGlobalStatusDistribution()
      .then(res => {
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data.map((d: any) => ({ ...d, total_count: Number(d.total_count) })));
        }
      })
      .catch(err => console.error(err))
      .finally(() => setLoading(false));
  }, []);

  const exportCSV = () => {
    const csv = ['Status,Quantity\n' + data.map(d => `"${d.status_name}",${d.total_count}`).join('\n')];
    const blob = new Blob(csv, { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'status_distribution.csv';
    link.click();
  };

  const renderLegend = (value: string, entry: any) => {
    return (
      <span 
        title={`Status: ${value} | Quantity: ${entry.payload.total_count}`} 
        style={{ color: '#333', fontWeight: 500, marginRight: '10px', cursor: 'help' }}
      >
        {value} ({entry.payload.total_count})
      </span>
    );
  };

  if (loading) return <div className="p-4">Loading status data...</div>;

  return (
    <div style={{ minWidth: 0, width: '100%', background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
      <div className="flex justify-content-between align-items-start mb-3">
        <h3 style={{ color: '#0053a1', margin: 0 }}>Global Status Distribution</h3>
        <Button icon="pi pi-download" className="p-button-sm p-button-rounded p-button-text p-button-success" onClick={exportCSV} tooltip="Export CSV" tooltipOptions={{position: 'top'}} />
      </div>

      {data.length === 0 ? (
        <div style={{ height: 350, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#888' }}>No data</div>
      ) : (
        <div style={{ width: '100%', height: 350, position: 'relative' }}>
          <div style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}>
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={data} dataKey="total_count" nameKey="status_name" cx="50%" cy="50%" innerRadius="30%" outerRadius="80%" paddingAngle={2} shape={StatusPieGradient}>
                  {data.map((entry: any, index: number) => (
                    <Cell key={`cell-${index}`} fill={STATUS_COLORS[entry.status_name] || FALLBACK_COLORS[index % FALLBACK_COLORS.length]} />
                  ))}
                </Pie>
                {/* Formatter para mostrar Rótulo Original : Valor */}
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