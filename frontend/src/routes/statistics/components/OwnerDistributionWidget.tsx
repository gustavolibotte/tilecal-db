import React, { useEffect, useState } from 'react';
import { PieChart, Pie, Tooltip, ResponsiveContainer, Cell, Legend } from 'recharts';
import { Button } from 'primereact/button';
import { SelectButton } from 'primereact/selectbutton';
import { Dialog } from 'primereact/dialog';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { useNavigate } from 'react-router-dom';
import { getEquipviewRoute } from '../../../utils/routes';
import DbClient from '../../../utils/DbClient';
import { exportChartAsImage } from '../../../utils/exportUtils';

const COLORS = ['#0053a1', '#3399ff', '#ff9900', '#33cc33', '#ff6666', '#99ccff', '#cce5ff'];

export const OwnerDistributionWidget: React.FC = () => {
  const navigate = useNavigate();
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [exportFormat, setExportFormat] = useState<'PNG' | 'PDF' | 'CSV'>('PNG');
  const exportOptions = ['PNG', 'PDF', 'CSV'];

  const [showModal, setShowModal] = useState(false);
  const [selectedOwner, setSelectedOwner] = useState<string>('');
  const [ownerEquipments, setOwnerEquipments] = useState<any[]>([]);
  const [loadingEquipments, setLoadingEquipments] = useState(false);

  useEffect(() => {
    DbClient.getOwnerDistribution()
      .then(res => {
        const responseData = res?.data?.data || res?.data || res;
        
        if (Array.isArray(responseData)) {
          setData(responseData.map((d: any) => ({ 
            ...d, 
            owner_name: d.owner_name || 'Unknown',
            total_count: Number(d.count || d.total_count || 0) 
          })));
        }
      })
      .catch(err => console.error("Error loading owner data:", err))
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

  const onPieClick = (pieData: any) => {
    const ownerName = pieData?.name || pieData?.payload?.owner_name;
    if (!ownerName) return;

    setSelectedOwner(ownerName);
    setShowModal(true);
    setLoadingEquipments(true);

    DbClient.get(`/api/equipment?owner_name=${encodeURIComponent(ownerName)}`)
      .then(res => {
        if (res && res.data) {
          setOwnerEquipments(res.data.data || res.data);
        }
      })
      .catch(err => console.error("Failed to load details", err))
      .finally(() => setLoadingEquipments(false));
  };

  const renderModalHeader = () => (
    <div className="flex justify-content-between align-items-center w-full pr-4">
      <span>Equipments owned by {selectedOwner}</span>
      <div className="flex gap-2">
        <Button 
          icon="pi pi-external-link" 
          label="View in Equipments Page" 
          className="p-button-sm p-button-outlined p-button-info" 
          onClick={() => navigate('/equipment', { state: { filters: { owner_name: selectedOwner } } })} 
        />
      </div>
    </div>
  );

  const idBodyTemplate = (rowData: any) => (
    <Button 
      label={rowData.id} 
      className="p-button-link p-0 m-0" 
      onClick={() => navigate(getEquipviewRoute(rowData.id))} 
    />
  );

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
              <PieChart>
                <Pie
                  data={data}
                  dataKey="total_count"
                  nameKey="owner_name"
                  cx="50%"
                  cy="50%"
                  outerRadius={100}
                  onClick={onPieClick}
                  style={{ cursor: 'pointer' }}
                  label={(props: any) => `${props.owner_name} (${((props.percent || 0) * 100).toFixed(1)}%)`}
                >
                  {data.map((_, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip formatter={(value) => [value, 'Quantity']} />
                <Legend layout="horizontal" verticalAlign="bottom" align="center" />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}

      <Dialog header={renderModalHeader()} visible={showModal} style={{ width: '80vw' }} onHide={() => setShowModal(false)} maximizable>
        <DataTable value={ownerEquipments} loading={loadingEquipments} paginator rows={10} emptyMessage="No equipments found for this owner." size="small" stripedRows>
          <Column field="id" header="ID" body={idBodyTemplate} sortable />
          <Column field="type_name" header="Type" sortable />
          <Column field="status_name" header="Status" sortable />
          <Column field="location_name" header="Location" sortable />
        </DataTable>
      </Dialog>
    </div>
  );
};