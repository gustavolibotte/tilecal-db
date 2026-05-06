import React, { useEffect, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import { Button } from 'primereact/button';
import { SelectButton } from 'primereact/selectbutton';
import { Dialog } from 'primereact/dialog';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { useNavigate } from 'react-router-dom';
import { getEquipviewRoute } from '../../../utils/routes';
import DbClient from '../../../utils/DbClient';
import { exportChartAsImage } from '../../../utils/exportUtils';

const COLORS = ['#0073e6', '#3399ff', '#66b2ff', '#99ccff', '#cce5ff'];

export const OwnerDistributionWidget: React.FC = () => {
  const navigate = useNavigate();
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [scale, setScale] = useState<'auto' | 'log'>('auto');
  const [exportFormat, setExportFormat] = useState<'PNG' | 'PDF' | 'CSV'>('PNG');
  const exportOptions = ['PNG', 'PDF', 'CSV'];

  const [showModal, setShowModal] = useState(false);
  const [selectedOwner, setSelectedOwner] = useState<string>('');
  const [ownerEquipments, setOwnerEquipments] = useState<any[]>([]);
  const [loadingEquipments, setLoadingEquipments] = useState(false);

  useEffect(() => {
    DbClient.getOwnerDistribution()
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
        const csv = ['Owner,Quantity\n' + data.map(d => `"${d.owner_name}",${d.total_count}`).join('\n')];
        const blob = new Blob(csv, { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'owner_distribution.csv';
        link.click();
    } else {
        exportChartAsImage('owner-chart', 'owner_distribution', exportFormat);
    }
  };

  const onBarClick = (barData: any) => {
    const ownerName = barData?.payload?.owner_name || barData?.owner_name;
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
      <span>Equipments of {selectedOwner}</span>
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
          <Button icon="pi pi-chart-bar" className={`p-button-sm p-button-rounded p-button-text ${scale === 'auto' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('auto')} />
          <Button icon="pi pi-chart-line" className={`p-button-sm p-button-rounded p-button-text ${scale === 'log' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('log')} />
          <SelectButton value={exportFormat} options={exportOptions} onChange={(e) => { if(e.value) setExportFormat(e.value) }} className="p-button-sm ml-3" style={{transform: 'scale(0.85)', transformOrigin: 'right'}} />
          <Button icon="pi pi-download" className="p-button-rounded p-button-success" onClick={handleExport} />
        </div>
      </div>

      {data.length === 0 ? (
        <div style={{ height: 350, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#888' }}>No data</div>
      ) : (
        <div style={{ width: '100%', height: 350, position: 'relative' }}>
          <div id="owner-chart" style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}>
            <ResponsiveContainer width="100%" height="100%">
              <BarChart layout="vertical" data={data} margin={{ top: 10, right: 30, left: 40, bottom: 20 }}>
                <CartesianGrid strokeDasharray="3 3" horizontal={true} vertical={false} />
                <XAxis type="number" scale={scale} domain={scale === 'log' ? [1, 'auto'] : [0, 'auto']} />
                <YAxis dataKey="owner_name" type="category" width={150} tick={{fontSize: 10}} />
                <Tooltip cursor={{fill: '#f5f5f5'}} />
                <Bar dataKey="total_count" name="Quantity" radius={[0, 4, 4, 0]} onClick={onBarClick} style={{ cursor: 'pointer' }}>
                  {data.map((_, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}

      <Dialog header={renderModalHeader()} visible={showModal} style={{ width: '80vw' }} onHide={() => setShowModal(false)} maximizable>
        <DataTable value={ownerEquipments} loading={loadingEquipments} paginator rows={10} size="small" stripedRows>
          <Column field="id" header="ID" body={idBodyTemplate} sortable />
          <Column field="type_name" header="Type" sortable />
          <Column field="status_name" header="Status" sortable />
          <Column field="location_name" header="Location" sortable />
        </DataTable>
      </Dialog>
    </div>
  );
};