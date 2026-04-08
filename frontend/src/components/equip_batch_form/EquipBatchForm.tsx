import { useMutation, useQuery } from '@tanstack/react-query';
import './equip_batch_form.css';
import { useRef, useState } from 'react';
import { toast } from 'react-toastify';
import { Button } from 'primereact/button';
import { Dropdown } from 'primereact/dropdown';
import { TabView, TabPanel } from 'primereact/tabview';
import { InputText } from 'primereact/inputtext';
import { InputNumber } from 'primereact/inputnumber';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Message } from 'primereact/message';
import Papa from 'papaparse';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';

interface EquipmentRow {
  id?: string;
  type_name: string;
  serial?: string;
  location_name?: string;
  status_name?: string;
  owner_name?: string;
  [key: string]: any;
}

interface ValidationResult {
  valid: boolean;
  details: Array<{
    rowIndex: number;
    valid: boolean;
    errors: Record<string, string>;
  }>;
}

export default function EquipBatchForm() {
  const [selectedType, setSelectedType] = useState<string | null>(null);
  const [stagingData, setStagingData] = useState<EquipmentRow[]>([]);
  const [validationResult, setValidationResult] = useState<ValidationResult | null>(null);
  const [isValidating, setIsValidating] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Range State
  const [rangeParams, setRangeParams] = useState({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
  const [commonFields, setCommonFields] = useState({ location_name: '', status_name: '', owner_name: '' });

  // Types Query
  const equipTypesQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', {}]
  });

  // Mutations utilizing the authenticated DbClient logic
  const validateMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment') + '/batch-validate')
  });

  const commitMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment_batch'))
  });

  const getFormatHelp = (type: string | null) => {
    switch(type) {
      case 'PMT': return 'Format: 7-digit numeric ID (e.g., 0110003) or 14-digit ATLAS barcode. Often starts with "0A" or "0B".';
      case 'Mainboard': return 'Format: Alphanumeric prefix followed by sequence. E.g., "MB-001".';
      case 'Active HV Divider (Short Cable)': return 'Format: 7 digits starting with "00". Usually identical to Manufacturer Serial.';
      case 'FENICS': return 'Format: Alphanumeric barcode. Usually maps directly from Serial.';
      default: return 'Format: Unique ATLAS identifier (Barcode or Serial). Max 14 characters.';
    }
  };

  const handleFileUpload = (e: any) => {
    const file = e.target.files[0];
    if (!file || !selectedType) return;
    Papa.parse(file, {
      header: true, skipEmptyLines: true,
      complete: (res) => {
        setStagingData(res.data.map((r: any) => ({ ...r, type_name: selectedType })));
        setValidationResult(null);
        toast.info(`${res.data.length} rows loaded from CSV.`);
      }
    });
    if (fileInputRef.current) fileInputRef.current.value = '';
  };

  const generateRange = () => {
    if (!selectedType) return;
    const batch = [];
    for (let i = rangeParams.start; i <= rangeParams.end; i++) {
      const id = `${rangeParams.prefix}${i.toString().padStart(rangeParams.padding, '0')}${rangeParams.suffix}`;
      batch.push({ id, type_name: selectedType, ...commonFields });
    }
    setStagingData(batch);
    setValidationResult(null);
    toast.info(`Generated batch of ${batch.length} items.`);
  };

  const runValidation = () => {
    setIsValidating(true);
    validateMutation.mutate({ equipments: stagingData }, {
      onSuccess: (res: any) => {
        const data = res.data;
        setValidationResult(data);
        if (data.valid) toast.success("All records valid! Ready to store.");
        else toast.error("Validation failed. Check rows in red.");
        setIsValidating(false);
      },
      onError: () => {
        toast.error("Validation request failed.");
        setIsValidating(false);
      }
    });
  };

  const commitBatch = () => {
    setIsSubmitting(true);
    commitMutation.mutate({ equipments: stagingData }, {
      onSuccess: () => {
        toast.success("Batch registration completed successfully!");
        setStagingData([]);
        setValidationResult(null);
        setIsSubmitting(false);
      },
      onError: (error: any) => {
        toast.error(error.response?.data?.messages?.error || "An error occurred during submission.");
        setIsSubmitting(false);
      }
    });
  };

  if (equipTypesQuery.isPending) return <p>Loading equipment types...</p>;
  if (equipTypesQuery.isError) return <p>Failed to load data.</p>;

  return (
    <div className="card p-4">
      <h2 className="text-2xl font-bold mb-4">Batch Equipment Manager</h2>
      
      <div className="mb-4">
        <label className="font-bold block mb-2">1. Select Equipment Category</label>
        <Dropdown value={selectedType} options={equipTypesQuery.data?.data?.map((t:any)=>({label:t.name, value:t.name})) || []} 
          onChange={(e) => {setSelectedType(e.value); setStagingData([]); setValidationResult(null);}} 
          placeholder="Select Type" className="w-full md:w-30rem" />
        {selectedType && <Message severity="info" text={getFormatHelp(selectedType)} className="mt-2 w-full justify-content-start" />}
      </div>

      <TabView className="mb-4">
        <TabPanel header="Import CSV" disabled={!selectedType}>
          <div className="flex align-items-center gap-3">
            <input type="file" accept=".csv" ref={fileInputRef} onChange={handleFileUpload} className="hidden" id="csv-upload" />
            <Button label="Load CSV File" icon="pi pi-file-excel" onClick={()=>document.getElementById('csv-upload')?.click()} outlined type="button" />
            <span className="text-sm text-gray-500 italic">Headers: id, serial, location_name, status_name, comments...</span>
          </div>
        </TabPanel>
        
        <TabPanel header="Generate by Range" disabled={!selectedType}>
          <div className="grid p-fluid align-items-end mb-3">
            <div className="col-12 md:col-2"><label className="text-sm">Prefix</label><InputText value={rangeParams.prefix} onChange={(e)=>setRangeParams({...rangeParams, prefix: e.target.value})} placeholder="MB-" /></div>
            <div className="col-12 md:col-2"><label className="text-sm">Start</label><InputNumber value={rangeParams.start} onValueChange={(e)=>setRangeParams({...rangeParams, start: e.value || 1})} /></div>
            <div className="col-12 md:col-2"><label className="text-sm">End</label><InputNumber value={rangeParams.end} onValueChange={(e)=>setRangeParams({...rangeParams, end: e.value || 1})} /></div>
            <div className="col-12 md:col-2"><label className="text-sm">Padding</label><InputNumber value={rangeParams.padding} onValueChange={(e)=>setRangeParams({...rangeParams, padding: e.value || 0})} placeholder="e.g. 3" /></div>
            <div className="col-12 md:col-4"><Button label="Generate IDs" icon="pi pi-cog" onClick={generateRange} severity="secondary" type="button" /></div>
          </div>
          <div className="grid p-fluid">
            <div className="col-12 md:col-6"><InputText placeholder="Common Location (optional)" value={commonFields.location_name} onChange={(e)=>setCommonFields({...commonFields, location_name: e.target.value})} /></div>
            <div className="col-12 md:col-6"><InputText placeholder="Common Status (optional)" value={commonFields.status_name} onChange={(e)=>setCommonFields({...commonFields, status_name: e.target.value})} /></div>
          </div>
        </TabPanel>
      </TabView>

      {stagingData.length > 0 && (
        <div className="mt-5 border-top-1 border-gray-200 pt-4">
          <div className="flex justify-content-between align-items-center mb-3">
            <h3 className="m-0">2. Staging Area: Verify {stagingData.length} records</h3>
            <Button label="Dry-Run Validation" icon="pi pi-shield" severity="warning" onClick={runValidation} loading={isValidating} type="button" />
          </div>

          <DataTable value={stagingData} showGridlines size="small" scrollHeight="400px" scrollable 
            rowClassName={(rowData) => {
              if (!validationResult) return '';
              const rowIndex = stagingData.indexOf(rowData);
              const status = validationResult.details[rowIndex];
              return status?.valid === false ? 'bg-red-50' : status?.valid ? 'bg-green-50' : '';
            }}>
            <Column header="Status" body={(_, opts) => {
              const res = validationResult?.details[opts.rowIndex];
              if (!res) return <i className="pi pi-circle text-gray-300" />;
              return res.valid ? <i className="pi pi-check-circle text-green-600" /> : <i className="pi pi-times-circle text-red-600" title={Object.values(res.errors).join(", ")} />;
            }} style={{width: '4rem', textAlign: 'center'}} />
            <Column field="id" header="ID / Barcode" />
            <Column field="serial" header="Serial No." />
            <Column field="location_name" header="Location" />
            <Column field="status_name" header="Status" />
          </DataTable>

          <div className="mt-4 flex justify-content-end gap-2">
            <Button label="Clear Staging" icon="pi pi-trash" severity="danger" text onClick={() => {setStagingData([]); setValidationResult(null);}} type="button" />
            <Button label="Commit to Database" icon="pi pi-database" severity="success" disabled={!validationResult?.valid} loading={isSubmitting} onClick={commitBatch} type="button" />
          </div>
        </div>
      )}
    </div>
  );
}