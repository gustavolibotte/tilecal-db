import React, { useEffect, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import DbClient from '../../../utils/DbClient';

const COLORS = ['#0073e6', '#3399ff', '#66b2ff', '#99ccff', '#cce5ff'];

export const LocationDistributionWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    DbClient.getLocationDistribution()
      .then(res => {
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data);
        }
      })
      .catch(err => console.error("Error fetching location data:", err))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="p-4">Loading location data...</div>;

  return (
    <div style={{ background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)', minWidth: 0 }}>
      <h3 style={{ color: '#0053a1', marginTop: 0 }}>Top 10 Locations</h3>
      <div style={{ width: '100%', height: 300 }}>
        <ResponsiveContainer width="100%" height="100%">
          <BarChart layout="vertical" data={data} margin={{ top: 20, right: 30, left: 40, bottom: 5 }}>
            <CartesianGrid strokeDasharray="3 3" horizontal={true} vertical={false} />
            <XAxis type="number" />
            <YAxis dataKey="location_name" type="category" width={100} tick={{fontSize: 12}} />
            <Tooltip cursor={{fill: '#f5f5f5'}} />
            <Bar dataKey="total_count" radius={[0, 4, 4, 0]}>
              {data.map((_entry, index) => (
                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
              ))}
            </Bar>
          </BarChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
};