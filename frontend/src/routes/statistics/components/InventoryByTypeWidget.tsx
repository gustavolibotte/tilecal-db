import React, { useEffect, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import DbClient from '../../../utils/DbClient';

const COLORS = ['#0053a1', '#0073e6', '#3399ff', '#66b2ff', '#99ccff'];

export const InventoryByTypeWidget: React.FC = () => {
  const [data, setData] = useState<any[]>([]); // Forçando o tipo para array
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    DbClient.getInventoryByType()
      .then(res => {
        // Camada de segurança: Só seta os dados se a resposta for um Array válido
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data);
        } else {
          console.warn("Invalid data received for inventory:", res);
          setData([]);
        }
      })
      .catch(err => console.error("Error fetching inventory data:", err))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="p-4">Loading inventory data...</div>;

  return (
    <div style={{ background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
      <h3 style={{ color: '#0053a1', marginTop: 0 }}>Equipments by Type</h3>
      <div style={{ width: '100%', height: 300 }}>
        <ResponsiveContainer>
          <BarChart data={data} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
            <CartesianGrid strokeDasharray="3 3" vertical={false} />
            <XAxis dataKey="type_name" />
            <YAxis />
            <Tooltip cursor={{fill: '#f5f5f5'}} />
            <Bar dataKey="total_count" radius={[4, 4, 0, 0]}>
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