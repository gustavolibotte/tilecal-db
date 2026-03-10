import React, { useEffect, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import DbClient from '../../../utils/DbClient';

// Cores de alerta (tons de laranja/âmbar) para indicar material em espera
const ALERT_COLORS = ['#e85c0f', '#f47b20', '#ff9933', '#ffb366', '#ffcc99'];

export const OrphanedComponentsWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    DbClient.getOrphanedComponents()
      .then(res => {
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data);
        }
      })
      .catch(err => console.error("Error fetching orphaned data:", err))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="p-4">Loading orphaned components...</div>;

  return (
    <div style={{ background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)', borderTop: '4px solid #e85c0f' }}>
      <div style={{ marginBottom: '1rem' }}>
        <h3 style={{ color: '#e85c0f', marginTop: 0, marginBottom: '4px' }}>Orphaned Components (Bottlenecks)</h3>
        <span style={{ fontSize: '0.85rem', color: '#666' }}>Components with 'Good' status not yet assembled.</span>
      </div>
      <div style={{ width: '100%', height: 300 }}>
        <ResponsiveContainer>
          <BarChart data={data} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
            <CartesianGrid strokeDasharray="3 3" vertical={false} />
            <XAxis dataKey="type_name" />
            <YAxis />
            <Tooltip cursor={{fill: '#fff3e6'}} />
            <Bar dataKey="total_count" radius={[4, 4, 0, 0]}>
              {data.map((_entry, index) => (
                <Cell key={`cell-${index}`} fill={ALERT_COLORS[index % ALERT_COLORS.length]} />
              ))}
            </Bar>
          </BarChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
};