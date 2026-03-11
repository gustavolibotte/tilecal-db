import React, { useEffect, useState } from 'react';
import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer, Legend } from 'recharts';
import DbClient from '../../../utils/DbClient';

const STATUS_COLORS: { [key: string]: string } = {
  'Good': '#008000',
  'Broken': '#c86400',
  'Under Test': '#0053a1',
  'To Be Repaired': '#ffcc00',
  'Lost': '#505050'
};

export const StatusDistributionWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    DbClient.getGlobalStatusDistribution()
      .then(res => {
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data);
        }
      })
      .catch(err => console.error("Error fetching status data:", err))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="p-4">Loading status data...</div>;

  return (
    <div style={{ background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)', minWidth: 0 }}>
      <h3 style={{ color: '#0053a1', marginTop: 0 }}>Global Status Distribution</h3>
      <div style={{ width: '100%', height: 300 }}>
        <ResponsiveContainer width="100%" height="100%">
          <PieChart>
            <Pie
              data={data}
              dataKey="total_count"
              nameKey="status_name"
              cx="50%"
              cy="50%"
              innerRadius={60}
              outerRadius={80}
              paddingAngle={5}
              label
            >
              {data.map((entry: any, index: number) => (
                <Cell 
                    key={`cell-${index}`} 
                    fill={STATUS_COLORS[entry.status_name] || '#8884d8'} 
                />
              ))}
            </Pie>
            <Tooltip />
            <Legend verticalAlign="bottom" height={36}/>
          </PieChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
};