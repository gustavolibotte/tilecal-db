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
import { Checkbox } from 'primereact/checkbox';
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
  _pattern_warning?: boolean;
  [key: string]: any;
}

interface ValidationResult {
  valid: boolean;
  hasWarnings?: boolean;
  details: Array<{
    rowIndex: number;
    valid: boolean;
    errors: Record<string, string>;
    warnings: Record<string, string>;
  }>;
}

export default function EquipBatchForm() {
  const [selectedType, setSelectedType] = useState<string | null>(null);
  const [stagingData, setStagingData] = useState<EquipmentRow[]>([]);
  const [validationResult, setValidationResult] = useState<ValidationResult | null>(null);
  const [isValidating, setIsValidating] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [forceCommitWarnings, setForceCommitWarnings] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Range & Smart Pattern State
  const [rangeParams, setRangeParams] = useState({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
  const [commonFields, setCommonFields] = useState<Record<string, string>>({});
  const [smartHint, setSmartHint] = useState<string>("Select an equipment type to load smart formatting rules.");
  const [currentRegex, setCurrentRegex] = useState<RegExp | null>(null);

  // --- 1. Fetch System Dictionaries via Global Query Client ---
  const equipTypesQuery: any = useQuery({ queryKey: ['equipment_type', 'get', {}] });
  const locationsQuery: any = useQuery({ queryKey: ['location', 'get', {}] });
  const statusesQuery: any = useQuery({ queryKey: ['status', 'get', {}] });
  const institutesQuery: any = useQuery({ queryKey: ['institute', 'get', {}] });

  const locations = useMemo(() => locationsQuery.data?.data?.map((l:any) => l.name) || [], [locationsQuery.data]);
  const statuses = useMemo(() => statusesQuery.data?.data?.map((s:any) => s.name) || [], [statusesQuery.data]);
  const institutes = useMemo(() => institutesQuery.data?.data?.map((i:any) => i.name) || [], [institutesQuery.data]);

  const selectedTypeObj = useMemo(() => {
    return equipTypesQuery.data?.data?.find((t: any) => t.name === selectedType);
  }, [selectedType, equipTypesQuery.data]);

  const dynamicParams: string[] = useMemo(() => {
    if (!selectedTypeObj?.params) return [];
    return typeof selectedTypeObj.params === 'string' ? JSON.parse(selectedTypeObj.params) : selectedTypeObj.params;
  }, [selectedTypeObj]);

  const standardFields = ['location_name', 'status_name', 'owner_name', 'comments'];

  // --- 2. Fetch Recent Samples & Build Smart Hint ---
  const recentEquipQuery: any = useQuery({
    queryKey: ['equipment', 'get', { type_name: selectedType, limit: 10, sortParams: JSON.stringify([{ field: 'created_at', order: -1 }]) }],
    enabled: !!selectedType
  });

  useEffect(() => {
    if (!selectedType) {
      setSmartHint("Select an equipment type to load smart formatting rules.");
      setCurrentRegex(null);
      return;
    }
    
    setCommonFields({});
    setForceCommitWarnings(false);

    if (recentEquipQuery.isSuccess) {
      const payload = recentEquipQuery.data?.data;
      const items = Array.isArray(payload) ? payload : payload?.data;
      const ids = (items || []).map((eq: any) => eq.id).filter(Boolean);
      
      if (ids.length > 0) {
        const sampleId = ids[0];
        const match = sampleId.match(/^([^0-9]*)(\d+)([^0-9]*)$/);
        
        let recentExamples = ids.slice(0, 3).join(', ');

        if (match) {
          const pre = match[1] || '';
          const suf = match[3] || '';
          const pad = match[2].length;
          
          setRangeParams({
            prefix: pre,
            start: parseInt(match[2], 10) + 1 || 1,
            end: parseInt(match[2], 10) + 10 || 10,
            padding: pad,
            suffix: suf
          });

          const escapeRegExp = (s: string) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          setCurrentRegex(new RegExp(`^${escapeRegExp(pre)}\\d{${pad}}${escapeRegExp(suf)}$`));

          let hintParts = [];
          if (pre) hintParts.push(`Prefix "${pre}"`);
          hintParts.push(`${pad} padding digits`);
          if (suf) hintParts.push(`Suffix "${suf}"`);

          setSmartHint(`💡 Smart Hint: The standard format uses ${hintParts.join(' and ')}. (Recent examples: ${recentExamples})`);
        } else {
          setRangeParams({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
          setCurrentRegex(null);
          setSmartHint(`💡 Smart Hint: Recent entries look like Barcodes or Serials (e.g., ${recentExamples}). Proceed manually.`);
        }
      } else {
        setRangeParams({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
        setCurrentRegex(null);
        setSmartHint(`No prior records found for ${selectedType}. You will establish the first formatting pattern!`);
      }
    }
  }, [selectedType, recentEquipQuery.isSuccess, recentEquipQuery.data]);

  // --- 3. Mutations ---
  const validateMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment') + '/batch-validate')
  });

  const commitMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment_batch'))
  });

  // --- 4. Handlers ---
  const downloadCSVTemplate = () => {
    if (!selectedType) return;
    const headers = ['id', 'serial', ...standardFields, ...dynamicParams];
    const hintRow = [
      'Fill ID', 'Fill Serial', 
      `Must be: ${locations.slice(0,3).join('|')}...`, 
      `Must be: ${statuses.slice(0,3).join('|')}...`, 
      `Must be: ${institutes.slice(0,3).join('|')}...`, 
      'Free text', ...dynamicParams.map(()=>'Value')
    ];
    
    const csvContent = headers.join(',') + "\n" + hintRow.join(','); 
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.setAttribute("href", URL.createObjectURL(blob));
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
        let validRows = res.data.filter((r:any) => !r.id?.includes('Fill ID'));
        setStagingData(validRows.map((r: any) => ({ ...r, type_name: selectedType })));
        setValidationResult(null);
        setForceCommitWarnings(false);
        toast.info(`${validRows.length} rows loaded from CSV.`);
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
    setForceCommitWarnings(false);
    toast.info(`Generated batch of ${batch.length} items.`);
  };

  const runValidation = () => {
    setIsValidating(true);
    setForceCommitWarnings(false);
    
    validateMutation.mutate({ equipments: stagingData }, {
      onSuccess: (res: any) => {
        const backendData = res.data;
        let globalHasWarnings = false;

        const enrichedDetails = backendData.details.map((rowStatus: any) => {
          const row = stagingData[rowStatus.rowIndex];
          const warnings: Record<string, string> = {};

          if (row.location_name && !locations.includes(row.location_name)) {
            rowStatus.valid = false;
            rowStatus.errors['location_name'] = `'${row.location_name}' is not a registered Location.`;
          }
          if (row.status_name && !statuses.includes(row.status_name)) {
            rowStatus.valid = false;
            rowStatus.errors['status_name'] = `'${row.status_name}' is not a registered Status.`;
          }
          if (row.owner_name && !institutes.includes(row.owner_name)) {
            rowStatus.valid = false;
            rowStatus.errors['owner_name'] = `'${row.owner_name}' is not a registered Institute.`;
          }

          if (rowStatus.valid && currentRegex && row.id && !currentRegex.test(row.id)) {
            warnings['id'] = `ID '${row.id}' deviates from the usual format.`;
            globalHasWarnings = true;
          }

          return { ...rowStatus, warnings };
        });

        const finalValid = enrichedDetails.every((d:any) => d.valid);

        setValidationResult({
          valid: finalValid,
          hasWarnings: globalHasWarnings,
          details: enrichedDetails
        });

        if (finalValid && !globalHasWarnings) toast.success("All records valid and follow standard patterns!");
        else if (finalValid && globalHasWarnings) toast.warn("Data is valid, but some IDs deviate from the standard pattern.");
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
        setForceCommitWarnings(false);
        recentEquipQuery.refetch();
      },
      onError: (error: any) => {
        toast.error(error.response?.data?.messages?.error || "An error occurred during submission.");
        setIsSubmitting(false);
      }
    });
  };

  if (equipTypesQuery.isPending) return <p>Loading system components...</p>;

  const previewColumns = ['id', 'serial', ...standardFields, ...dynamicParams];

  const getRowClass = (rowData: any) => {
    if (!validationResult) return '';
    const rowIndex = stagingData.indexOf(rowData);
    const status = validationResult.details[rowIndex];
    if (status?.valid === false) return 'bg-red-50'; 
    if (Object.keys(status?.warnings || {}).length > 0) return 'bg-yellow-50'; 
    if (status?.valid) return 'bg-green-50'; 
    return '';
  };

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
        
        <TabPanel header="Method B: Generate Range" disabled={!selectedType}>
          <div className="surface-100 p-3 border-round mb-3">
            <h4 className="m-0 mb-3 text-700">ID Generation Pattern</h4>
            <div className="grid p-fluid align-items-end">
              <div className="col-12 md:col-2"><label className="text-sm">Prefix</label><InputText value={rangeParams.prefix} onChange={(e)=>setRangeParams({...rangeParams, prefix: e.target.value})} placeholder="e.g. MB-" /></div>
              <div className="col-12 md:col-2"><label className="text-sm">Start Value</label><InputNumber value={rangeParams.start} onValueChange={(e)=>setRangeParams({...rangeParams, start: e.value || 1})} useGrouping={false} /></div>
              <div className="col-12 md:col-2"><label className="text-sm">End Value</label><InputNumber value={rangeParams.end} onValueChange={(e)=>setRangeParams({...rangeParams, end: e.value || 1})} useGrouping={false} /></div>
              <div className="col-12 md:col-2"><label className="text-sm">Zero Padding</label><InputNumber value={rangeParams.padding} onValueChange={(e)=>setRangeParams({...rangeParams, padding: e.value || 0})} useGrouping={false} placeholder="e.g. 3" /></div>
              <div className="col-12 md:col-2"><label className="text-sm">Suffix</label><InputText value={rangeParams.suffix} onChange={(e)=>setRangeParams({...rangeParams, suffix: e.target.value})} placeholder="e.g. -v2" /></div>
            </div>
          </div>

          <h4 className="mb-2 text-700">Replicated Data (Applied to all items)</h4>
          <div className="grid p-fluid">
            <div className="col-12 md:col-3">
              <label className="text-sm">Location</label>
              <Dropdown value={commonFields['location_name']} options={locations} onChange={(e)=>setCommonFields({...commonFields, location_name: e.value})} showClear placeholder="Select Location" />
            </div>
            <div className="col-12 md:col-3">
              <label className="text-sm">Status</label>
              <Dropdown value={commonFields['status_name']} options={statuses} onChange={(e)=>setCommonFields({...commonFields, status_name: e.value})} showClear placeholder="Select Status" />
            </div>
            <div className="col-12 md:col-3">
              <label className="text-sm">Owner Institute</label>
              <Dropdown value={commonFields['owner_name']} options={institutes} onChange={(e)=>setCommonFields({...commonFields, owner_name: e.value})} showClear placeholder="Select Institute" />
            </div>
            <div className="col-12 md:col-3">
              <label className="text-sm">Comments</label>
              <InputText value={commonFields['comments'] || ''} onChange={(e)=>setCommonFields({...commonFields, comments: e.target.value})} />
            </div>

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

      {stagingData.length > 0 && (
        <div className="mt-5 border-top-1 border-300 pt-4">
          <div className="flex justify-content-between align-items-center mb-3">
            <div>
              <h3 className="m-0 text-800">2. Staging Area: Verify Data</h3>
              <span className="text-500 text-sm">{stagingData.length} records ready for inspection.</span>
            </div>
            <Button label="Run Rigorous Validation" icon="pi pi-shield" severity="warning" onClick={runValidation} loading={isValidating} type="button" />
          </div>

          {validationResult?.hasWarnings && (
            <Message severity="warn" text="Warning: Some IDs deviate from the recent historical pattern. Please review the yellow rows carefully." className="mb-3 w-full justify-content-start" />
          )}

          <DataTable value={stagingData} showGridlines size="small" scrollHeight="400px" scrollable rowClassName={getRowClass}>
            <Column header="Validation" body={(d) => {
              const res = validationResult?.details[stagingData.indexOf(d)];
              if (!res) return <i className="pi pi-circle text-gray-300" />;
              
              if (!res.valid) {
                return <i className="pi pi-times-circle text-red-600 text-xl" title={Object.values(res.errors).join(" | ")} />;
              }
              if (Object.keys(res.warnings).length > 0) {
                return <i className="pi pi-exclamation-triangle text-yellow-600 text-xl" title={Object.values(res.warnings).join(" | ")} />;
              }
              return <i className="pi pi-check-circle text-green-600 text-xl" title="Perfect" />;
            }} style={{width: '5rem', textAlign: 'center'}} />
            
            {previewColumns.map(col => (
              <Column key={col} field={col} header={snakeToSpaced(col)} style={{ minWidth: '150px' }} />
            ))}
          </DataTable>

          <div className="mt-4 flex flex-column align-items-end gap-3">
            {validationResult?.valid && validationResult?.hasWarnings && (
               <div className="flex align-items-center">
                  <Checkbox inputId="forceAck" checked={forceCommitWarnings} onChange={e => setForceCommitWarnings(e.checked || false)} />
                  <label htmlFor="forceAck" className="ml-2 text-yellow-700 font-bold">I confirm I want to store records that deviate from the standard ID format.</label>
               </div>
            )}
            
            <div className="flex gap-3">
              <Button label="Clear Staging" icon="pi pi-trash" severity="danger" text onClick={() => {setStagingData([]); setValidationResult(null);}} type="button" />
              <Button label="Commit to Database" icon="pi pi-database" severity="success" 
                disabled={!validationResult?.valid || (validationResult?.hasWarnings && !forceCommitWarnings)} 
                loading={isSubmitting} onClick={commitBatch} type="button" />
            </div>
          </div>
        </div>
      )}
    </div>
  );
}