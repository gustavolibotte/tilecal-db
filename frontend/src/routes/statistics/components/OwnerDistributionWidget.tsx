import React, { useEffect, useState } from 'react';
import DbClient from '../../../utils/DbClient';
import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer, Legend } from 'recharts'; // Adapte se usar Chart.js

export const OwnerDistributionWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    DbClient.getOwnerDistribution()
      .then((res: any) => {
        const chartData = res.data || res;
        
        const formattedData = chartData.map((item: any) => ({
          name: item.owner_name || 'Unknown',
          count: parseInt(item.count, 10)
        }));

        setData(formattedData);
        setLoading(false);
      })
      .catch((err: any) => {
        console.error("Erro ao buscar dados de owners:", err);
        setLoading(false);
      });
  }, []);

  const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8', '#E06C75'];

  if (loading) return <div>Loading Owner Distribution...</div>;

  return (
    <div className="widget-container" style={{ padding: '1rem', backgroundColor: '#fff', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
      <h3 style={{ textAlign: 'center', marginBottom: '1rem' }}>Distribution by Owner</h3>
      <div style={{ width: '100%', height: 300 }}>
        <ResponsiveContainer>
          <PieChart>
            <Pie
              data={data}
              dataKey="count"
              nameKey="name"
              cx="50%"
              cy="50%"
              outerRadius={100}
              label
            >
              {data.map((_, index) => (
                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                ))}
            </Pie>
            <Tooltip />
            <Legend />
          </PieChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
};