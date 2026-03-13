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

export const LocationDistributionWidget: React.FC = () => {
  const navigate = useNavigate();
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [scale, setScale] = useState<'auto' | 'log'>('auto');
  const [exportFormat, setExportFormat] = useState<'PNG' | 'PDF' | 'CSV'>('PNG');
  const exportOptions = ['PNG', 'PDF', 'CSV'];

  const [showModal, setShowModal] = useState(false);
  const [selectedLocation, setSelectedLocation] = useState<string>('');
  const [locationEquipments, setLocationEquipments] = useState<any[]>([]);
  const [loadingEquipments, setLoadingEquipments] = useState(false);

  useEffect(() => {
    DbClient.getLocationDistribution()
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
        const csv = ['Location,Quantity\n' + data.map(d => `"${d.location_name}",${d.total_count}`).join('\n')];
        const blob = new Blob(csv, { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'location_distribution.csv';
        link.click();
    } else {
        exportChartAsImage('location-chart', 'location_distribution', exportFormat);
    }
  };

  const onBarClick = (barData: any) => {
    const locName = barData?.payload?.location_name || barData?.location_name;
    if (!locName) return;

    setSelectedLocation(locName);
    setShowModal(true);
    setLoadingEquipments(true);

    DbClient.getEquipmentsByLocation(locName)
      .then(res => {
        if (res && res.data) {
          setLocationEquipments(res.data);
        }
      })
      .catch(err => console.error("Failed to load details", err))
      .finally(() => setLoadingEquipments(false));
  };

  const idBodyTemplate = (rowData: any) => {
    return (
      <Button 
        label={rowData.equipment_id} 
        className="p-button-link p-0 m-0" 
        style={{ padding: 0 }}
        onClick={() => navigate(getEquipviewRoute(rowData.equipment_id))} 
        tooltip="View Equipment Details"
        tooltipOptions={{position: 'top'}}
      />
    );
  };

  if (loading) return <div className="p-4">Loading location data...</div>;

  return (
    <div style={{ minWidth: 0, width: '100%', background: '#fff', padding: '20px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
      <div className="flex justify-content-between align-items-start mb-3">
        <h3 style={{ color: '#0053a1', margin: 0 }}>Top 10 Locations</h3>
        <div className="flex align-items-center gap-2">
          <Button icon="pi pi-chart-bar" className={`p-button-sm p-button-rounded p-button-text ${scale === 'auto' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('auto')} tooltip="Linear Scale" tooltipOptions={{position: 'top'}} />
          <Button icon="pi pi-chart-line" className={`p-button-sm p-button-rounded p-button-text ${scale === 'log' ? 'p-button-info' : 'p-button-plain'}`} onClick={() => setScale('log')} tooltip="Logarithmic Scale" tooltipOptions={{position: 'top'}} />
          <SelectButton value={exportFormat} options={exportOptions} onChange={(e) => { if(e.value) setExportFormat(e.value) }} className="p-button-sm ml-3" style={{transform: 'scale(0.85)', transformOrigin: 'right'}} />
          <Button icon="pi pi-download" className="p-button-rounded p-button-success" onClick={handleExport} tooltip={`Export as ${exportFormat}`} tooltipOptions={{position: 'top'}} />
        </div>
      </div>

      {data.length === 0 ? (
        <div style={{ height: 350, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#888' }}>No data</div>
      ) : (
        <div style={{ width: '100%', height: 350, position: 'relative' }}>
          <div id="location-chart" style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}>
            <ResponsiveContainer width="100%" height="100%">
              <BarChart layout="vertical" data={data} margin={{ top: 10, right: 30, left: 40, bottom: 20 }}>
                <CartesianGrid strokeDasharray="3 3" horizontal={true} vertical={false} />
                <XAxis type="number" scale={scale} domain={scale === 'log' ? [1, 'auto'] : [0, 'auto']} allowDataOverflow label={{ value: 'Quantity', position: 'bottom', offset: 0 }} />
                <YAxis dataKey="location_name" type="category" width={80} tick={{fontSize: 12}} />
                <Tooltip cursor={{fill: '#f5f5f5'}} formatter={(value) => [value, 'Quantity']} />
                <Bar dataKey="total_count" name="Quantity" radius={[0, 4, 4, 0]} onClick={onBarClick} style={{ cursor: 'pointer' }}>
                  {data.map((_entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}

      <Dialog header={`Equipments in ${selectedLocation}`} visible={showModal} style={{ width: '70vw' }} onHide={() => setShowModal(false)} maximizable>
        <DataTable value={locationEquipments} loading={loadingEquipments} paginator rows={10} rowsPerPageOptions={[5, 10, 25, 50]} emptyMessage="No equipments found in this location." size="small" stripedRows>
          <Column field="equipment_id" header="ID" body={idBodyTemplate} sortable />
          <Column field="type_name" header="Type" sortable />
          <Column field="status_name" header="Status" sortable />
          <Column field="owner_name" header="Owner / Institute" sortable />
        </DataTable>
      </Dialog>
    </div>
  );
};