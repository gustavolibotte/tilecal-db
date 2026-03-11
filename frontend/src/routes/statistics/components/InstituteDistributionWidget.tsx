import React, { useEffect, useState } from 'react';
import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer, Legend } from 'recharts';
import DbClient from '../../../utils/DbClient';

const INSTITUTE_COLORS = ['#0053a1', '#e85c0f', '#008000', '#f4b400', '#6050dc'];

export const InstituteDistributionWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    DbClient.getInstituteDistribution()
      .then(res => {
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data);
        }
      })
      .catch(err => console.error("Error fetching institute data:", err))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="p-4">Loading institute data...</div>;

  return (
    <div style={{ background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)', minWidth: 0 }}>
      <h3 style={{ color: '#0053a1', marginTop: 0 }}>Distribution by Institute</h3>
      <div style={{ width: '100%' }}>
        <ResponsiveContainer width="100%" height={300}>
          <PieChart>
            <Pie
              data={data}
              dataKey="total_count"
              nameKey="institute_name"
              cx="50%"
              cy="50%"
              innerRadius={70}
              outerRadius={90}
              paddingAngle={2}
              label
            >
              {data.map((_entry, index) => (
                <Cell key={`cell-${index}`} fill={INSTITUTE_COLORS[index % INSTITUTE_COLORS.length]} />
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