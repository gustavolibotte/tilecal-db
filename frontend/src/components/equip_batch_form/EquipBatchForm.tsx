import { useMutation, useQuery } from '@tanstack/react-query';
import './equip_batch_form.css';
import { useEffect, useRef, useState, useMemo } from 'react';
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
import { snakeToSpaced } from '../../utils/string';

interface EquipmentRow {
  id?: string;
  type_name: string;
  serial?: string;
  location_name?: string;
  status_name?: string;
  owner_name?: string;
  comments?: string;
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

  // Range & Smart Pattern State
  const [rangeParams, setRangeParams] = useState({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
  const [commonFields, setCommonFields] = useState<Record<string, string>>({});
  const [smartHint, setSmartHint] = useState<string>("Select an equipment type to load smart formatting rules.");

  // --- 1. Fetch Equipment Types and Dynamic Columns ---
  const equipTypesQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', {}]
  });

  const selectedTypeObj = useMemo(() => {
    return equipTypesQuery.data?.data?.find((t: any) => t.name === selectedType);
  }, [selectedType, equipTypesQuery.data]);

  const dynamicParams: string[] = useMemo(() => {
    if (!selectedTypeObj?.params) return [];
    return typeof selectedTypeObj.params === 'string' ? JSON.parse(selectedTypeObj.params) : selectedTypeObj.params;
  }, [selectedTypeObj]);

  const standardFields = ['location_name', 'status_name', 'owner_name', 'comments'];

  // --- 2. Fetch Recent Samples via React Query for Smart Pattern Inference ---
  const recentEquipQuery: any = useQuery({
    queryKey: ['equipment', 'get', { 
      type_name: selectedType, 
      limit: 10, 
      sortParams: JSON.stringify([{ field: 'created_at', order: -1 }]) 
    }],
    enabled: !!selectedType
  });

  useEffect(() => {
    if (!selectedType) {
      setSmartHint("Select an equipment type to load smart formatting rules.");
      return;
    }
    
    // Reset common fields when type changes
    setCommonFields({});

    if (recentEquipQuery.isSuccess) {
      // Handle pagination wrapper if it exists (data.data.data vs data.data)
      const payload = recentEquipQuery.data?.data;
      const items = Array.isArray(payload) ? payload : payload?.data;
      const ids = (items || []).map((eq: any) => eq.id).filter(Boolean);
      
      if (ids.length > 0) {
        const sampleId = ids[0];
        // Classic Regex to extract Prefix, Number, and Suffix (e.g. MB-004-v2)
        const match = sampleId.match(/^([^0-9]*)(\d+)([^0-9]*)$/);
        
        if (match) {
          setRangeParams({
            prefix: match[1] || '',
            start: parseInt(match[2], 10) + 1 || 1,
            end: parseInt(match[2], 10) + 10 || 10,
            padding: match[2].length,
            suffix: match[3] || ''
          });
          setSmartHint(`💡 Smart Hint: Based on recent entries like "${sampleId}", the format uses Prefix "${match[1]}", ${match[2].length} padding digits, and Suffix "${match[3]}".`);
        } else {
          setRangeParams({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
          setSmartHint(`💡 Smart Hint: Recent entries look like "${sampleId}" (Barcodes or Serials without sequential logic). Proceed manually.`);
        }
      } else {
        setRangeParams({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
        setSmartHint(`No prior records found for ${selectedType}. You will establish the first formatting pattern!`);
      }
    } else if (recentEquipQuery.isError) {
       setSmartHint("Could not fetch previous records for format suggestions.");
    }
  }, [selectedType, recentEquipQuery.isSuccess, recentEquipQuery.data, recentEquipQuery.isError]);

  // --- 3. Mutations ---
  const validateMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment') + '/batch-validate')
  });

  const commitMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment_batch'))
  });

  // --- 4. CSV Template Downloader ---
  const downloadCSVTemplate = () => {
    if (!selectedType) return;
    const headers = ['id', 'serial', ...standardFields, ...dynamicParams];
    const csvContent = headers.join(',') + "\n" + headers.map(() => '').join(','); // empty second row
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `${selectedType.replace(/\s+/g, '_').toLowerCase()}_template.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const handleFileUpload = (e: any) => {
    const file = e.target.files[0];
    if (!file || !selectedType) return;
    Papa.parse(file, {
      header: true, skipEmptyLines: true,
      complete: (res: any) => {
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
        setValidationResult(res.data);
        if (res.data.valid) toast.success("All records valid! Ready to store.");
        else toast.error("Validation failed. Check rows in red.");
        setIsValidating(false);
      },
      onError: () => { toast.error("Validation request failed."); setIsValidating(false); }
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

  if (equipTypesQuery.isPending) return <p>Loading system components...</p>;
  if (equipTypesQuery.isError) return <p>Failed to load equipment data.</p>;

  // Combine standard and dynamic columns for Preview table
  const previewColumns = ['id', 'serial', ...standardFields, ...dynamicParams];

  return (
    <div className="card p-4 shadow-2 border-round">
      <h2 className="text-2xl font-bold mb-4 text-primary">Batch Equipment Manager</h2>
      
      <div className="mb-4">
        <label className="font-bold block mb-2 text-700">1. Select Equipment Category</label>
        <Dropdown value={selectedType} options={equipTypesQuery.data?.data?.map((t:any)=>({label:t.name, value:t.name})) || []} 
          onChange={(e) => {setSelectedType(e.value); setStagingData([]); setValidationResult(null);}} 
          placeholder="Choose the Equipment Type" className="w-full md:w-30rem" />
        {selectedType && <Message severity="info" text={smartHint} className="mt-3 w-full justify-content-start" />}
      </div>

      <TabView className="mb-4">
        {/* TAB A: CSV UPLOAD */}
        <TabPanel header="Method A: Import CSV" disabled={!selectedType}>
          <div className="flex flex-column gap-3">
            <p className="m-0 text-600">The template below includes all mandatory and specific columns for <b>{selectedType}</b>.</p>
            <div className="flex gap-3">
              <Button label="1. Download CSV Template" icon="pi pi-download" severity="info" outlined onClick={downloadCSVTemplate} type="button" />
              
              <input type="file" accept=".csv" ref={fileInputRef} onChange={handleFileUpload} className="hidden" id="csv-upload" />
              <Button label="2. Upload Filled CSV" icon="pi pi-upload" severity="secondary" onClick={()=>document.getElementById('csv-upload')?.click()} type="button" />
            </div>
          </div>
        </TabPanel>
        
        {/* TAB B: GENERATE RANGE */}
        <TabPanel header="Method B: Generate Range" disabled={!selectedType}>
          <div className="surface-100 p-3 border-round mb-3">
            <h4 className="m-0 mb-3 text-700">ID Generation Pattern</h4>
            <div className="grid p-fluid align-items-end">
              <div className="col-12 md:col-2"><label className="text-sm">Prefix</label><InputText value={rangeParams.prefix} onChange={(e)=>setRangeParams({...rangeParams, prefix: e.target.value})} placeholder="e.g. MB-" /></div>
              <div className="col-12 md:col-2"><label className="text-sm">Start Value</label><InputNumber value={rangeParams.start} onValueChange={(e)=>setRangeParams({...rangeParams, start: e.value || 1})} /></div>
              <div className="col-12 md:col-2"><label className="text-sm">End Value</label><InputNumber value={rangeParams.end} onValueChange={(e)=>setRangeParams({...rangeParams, end: e.value || 1})} /></div>
              <div className="col-12 md:col-2"><label className="text-sm">Zero Padding</label><InputNumber value={rangeParams.padding} onValueChange={(e)=>setRangeParams({...rangeParams, padding: e.value || 0})} placeholder="e.g. 3" /></div>
              <div className="col-12 md:col-2"><label className="text-sm">Suffix</label><InputText value={rangeParams.suffix} onChange={(e)=>setRangeParams({...rangeParams, suffix: e.target.value})} placeholder="e.g. -v2" /></div>
            </div>
          </div>

          <h4 className="mb-2 text-700">Replicated Data (Applied to all items)</h4>
          <div className="grid p-fluid">
            {/* Standard Fields */}
            {standardFields.map(field => (
              <div className="col-12 md:col-3" key={field}>
                <label className="text-sm capitalize">{snakeToSpaced(field)}</label>
                <InputText value={commonFields[field] || ''} onChange={(e)=>setCommonFields({...commonFields, [field]: e.target.value})} />
              </div>
            ))}
            {/* Dynamic Specific Params (Beta, Nominal HV, etc) */}
            {dynamicParams.map(field => (
              <div className="col-12 md:col-3" key={field}>
                <label className="text-sm capitalize text-blue-600 font-semibold">{snakeToSpaced(field)}</label>
                <InputText value={commonFields[field] || ''} onChange={(e)=>setCommonFields({...commonFields, [field]: e.target.value})} />
              </div>
            ))}
            
            <div className="col-12 flex justify-content-end mt-2">
              <Button label="Generate Batch" icon="pi pi-bolt" onClick={generateRange} type="button" />
            </div>
          </div>
        </TabPanel>
      </TabView>

      {/* STAGING AREA */}
      {stagingData.length > 0 && (
        <div className="mt-5 border-top-1 border-300 pt-4">
          <div className="flex justify-content-between align-items-center mb-3">
            <div>
              <h3 className="m-0 text-800">2. Staging Area: Verify Data</h3>
              <span className="text-500 text-sm">{stagingData.length} records ready for inspection.</span>
            </div>
            <Button label="Run Dry-Run Validation" icon="pi pi-shield" severity="warning" onClick={runValidation} loading={isValidating} type="button" />
          </div>

          <DataTable value={stagingData} showGridlines size="small" scrollHeight="400px" scrollable 
            rowClassName={(rowData) => {
              if (!validationResult) return '';
              const rowIndex = stagingData.indexOf(rowData);
              const status = validationResult.details[rowIndex];
              return status?.valid === false ? 'bg-red-50' : status?.valid ? 'bg-green-50' : '';
            }}>
            
            <Column header="Validation" body={(d) => {
              const res = validationResult?.details[stagingData.indexOf(d)];
              if (!res) return <i className="pi pi-circle text-gray-300" />;
              return res.valid 
                ? <i className="pi pi-check-circle text-green-600" title="Valid" /> 
                : <i className="pi pi-times-circle text-red-600" title={Object.values(res.errors).join(" | ")} />;
            }} style={{width: '5rem', textAlign: 'center'}} />
            
            {/* Render all dynamic columns so user sees exactly what is going to DB */}
            {previewColumns.map(col => (
              <Column key={col} field={col} header={snakeToSpaced(col)} style={{ minWidth: '150px' }} />
            ))}
          </DataTable>

          <div className="mt-4 flex justify-content-end gap-3">
            <Button label="Clear Staging" icon="pi pi-trash" severity="danger" text onClick={() => {setStagingData([]); setValidationResult(null);}} type="button" />
            <Button label="Commit to Database" icon="pi pi-database" severity="success" disabled={!validationResult?.valid} loading={isSubmitting} onClick={commitBatch} type="button" />
          </div>
        </div>
      )}
    </div>
  );
}