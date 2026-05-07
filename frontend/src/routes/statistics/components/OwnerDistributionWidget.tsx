import React, { useEffect, useState } from 'react';
import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer, Legend, Sector } from 'recharts';
import { Button } from 'primereact/button';
import { SelectButton } from 'primereact/selectbutton';
import { Dialog } from 'primereact/dialog';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { useNavigate } from 'react-router-dom';
import { getEquipviewRoute } from '../../../utils/routes';
import DbClient, { defaultQueryFunction } from '../../../utils/DbClient';
import { exportChartAsImage } from '../../../utils/exportUtils';

const OWNER_COLORS = ['#0053a1', '#e85c0f', '#008000', '#f4b400', '#6050dc', '#dc3545', '#17a2b8', '#6c757d'];

const PieGradient = (props: any) => {
  const { cx, cy, outerRadius, index, fill, isActive } = props; 
  return (
    <>
      <defs>
        <radialGradient id={`fillGradOwn${index}`} cx={cx} cy={cy} r={outerRadius} gradientUnits="userSpaceOnUse">
          <stop offset="0%" stopColor={fill} stopOpacity={0.1} />
          <stop offset="100%" stopColor={fill} stopOpacity={0.8} />
        </radialGradient>
        <clipPath id={`clipOwn${index}`}><Sector {...props} /></clipPath>
      </defs>
      <Sector 
        {...props} 
        clipPath={`url(#clipOwn${index})`} 
        fill={`url(#fillGradOwn${index})`} 
        stroke={fill} 
        strokeWidth={isActive ? 2 : 1} 
        style={{ cursor: 'pointer' }}
      />
    </>
  );
};

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
        if (res && res.data && Array.isArray(res.data)) {
          setData(res.data.map((d: any) => ({ ...d, total_count: Number(d.total_count) })));
        }
      })
      .catch(err => console.error("API Error:", err))
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
    const ownerName = pieData?.payload?.owner_name || pieData?.owner_name || pieData?.name;
    if (!ownerName) return;

    setSelectedOwner(ownerName);
    setShowModal(true);
    setLoadingEquipments(true);

    defaultQueryFunction({ 
        queryKey: ['equipment', 'get', { owner_name: ownerName }] 
    })
      .then((res: any) => {
        const fetchedData = res?.data?.data || res?.data || [];
        setOwnerEquipments(Array.isArray(fetchedData) ? fetchedData : []);
      })
      .catch(err => console.error("Error fetching equipments by owner:", err))
      .finally(() => setLoadingEquipments(false));
  };

  const renderLegend = (value: string, entry: any) => (
    <span title={`Owner: ${value} | Quantity: ${entry.payload.total_count}`} style={{ color: '#333', fontWeight: 500, marginRight: '10px', cursor: 'help' }}>
      {value} ({entry.payload.total_count})
    </span>
  );

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
                  innerRadius="25%" 
                  outerRadius="75%" 
                  paddingAngle={2} 
                  shape={PieGradient}
                  onClick={onPieClick}
                >
                  {data.map((_entry, index) => (
                    <Cell key={`cell-${index}`} fill={OWNER_COLORS[index % OWNER_COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip formatter={(value, name) => [value, name]} />
                <Legend formatter={renderLegend} verticalAlign="bottom" height={40}/>
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}

      <Dialog header={renderModalHeader()} visible={showModal} style={{ width: '80vw' }} onHide={() => setShowModal(false)} maximizable>
        <DataTable value={ownerEquipments} loading={loadingEquipments} paginator rows={10} emptyMessage="No equipments found." size="small" stripedRows>
          <Column field="id" header="ID" body={idBodyTemplate} sortable />
          <Column field="type_name" header="Type" sortable />
          <Column field="status_name" header="Status" sortable />
          <Column field="location_name" header="Location" sortable />
        </DataTable>
      </Dialog>
    </div>
  );
};