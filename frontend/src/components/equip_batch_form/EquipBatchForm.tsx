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
import { Column, ColumnEditorOptions } from 'primereact/column';
import { Message } from 'primereact/message';
import { Checkbox } from 'primereact/checkbox';
import ExcelJS from 'exceljs';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';

import { snakeToSpaced, applyAtlasBarcodeMask } from '../../utils/string'; 

interface EquipmentRow {
  id?: string;
  type_name: string;
  serial?: string;
  location_name?: string;
  status_name?: string;
  owner_name?: string;
  owner_mne?: string;
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

  const [rangeParams, setRangeParams] = useState({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
  const [copyIdToSerial, setCopyIdToSerial] = useState(true); 
  const [commonFields, setCommonFields] = useState<Record<string, string>>({});
  
  const [smartHint, setSmartHint] = useState<string>("Select an equipment type to load smart formatting rules.");
  const [currentRegex, setCurrentRegex] = useState<RegExp | null>(null);

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
    setStagingData([]);
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
          setRangeParams({ prefix: pre, start: parseInt(match[2], 10) + 1 || 1, end: parseInt(match[2], 10) + 10 || 10, padding: pad, suffix: suf });
          const escapeRegExp = (s: string) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          setCurrentRegex(new RegExp(`^${escapeRegExp(pre)}\\d{${pad}}${escapeRegExp(suf)}$`));

          let hintParts = [];
          if (pre) hintParts.push(`Prefix "${pre}"`);
          hintParts.push(`${pad} digits`);
          if (suf) hintParts.push(`Suffix "${suf}"`);
          setSmartHint(`Hint: The standard format uses ${hintParts.join(' and ')}. (Recent examples: ${recentExamples})`);
        } else {
          setRangeParams({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
          setCurrentRegex(null);
          setSmartHint(`Hint: Recent entries look like Barcodes/Serials (e.g., ${recentExamples}). Proceed manually.`);
        }
      } else {
        setRangeParams({ prefix: '', start: 1, end: 10, padding: 3, suffix: '' });
        setCurrentRegex(null);
        setSmartHint(`No prior records found for ${selectedType}. You will establish the first formatting pattern!`);
      }
    }
  }, [selectedType, recentEquipQuery.isSuccess, recentEquipQuery.data]);

  const validateMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment') + '/batch-validate')
  });

  const commitMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment_batch'))
  });

  const prepareDataForBackend = (data: EquipmentRow[]) => {
    return data.map(row => {
      let owner_mne = '';
      if (row.owner_name) {
        const inst = institutesQuery.data?.data?.find((i:any) => i.name === row.owner_name);
        if (inst) owner_mne = inst.mne;
      }
      return { ...row, owner_mne };
    });
  };

  const downloadExcelTemplate = async () => {
    if (!selectedType) return;
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet('Template');
    const listSheet = workbook.addWorksheet('Hidden_Lists', { state: 'hidden' }); 

    locations.forEach((loc: string, i: number) => listSheet.getCell(`A${i + 1}`).value = loc);
    statuses.forEach((stat: string, i: number) => listSheet.getCell(`B${i + 1}`).value = stat);
    institutes.forEach((inst: string, i: number) => listSheet.getCell(`C${i + 1}`).value = inst);

    const columns = [
      { header: 'id', key: 'id', width: 25 },
      { header: 'serial', key: 'serial', width: 25 },
      { header: 'location_name', key: 'location_name', width: 30 },
      { header: 'status_name', key: 'status_name', width: 25 },
      { header: 'owner_name', key: 'owner_name', width: 30 },
      { header: 'comments', key: 'comments', width: 40 },
      ...dynamicParams.map(p => ({ header: p, key: p, width: 25 }))
    ];
    sheet.columns = columns;

    sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
    sheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF00549F' } }; 
    if (smartHint) sheet.getCell('A1').note = smartHint.replace('Hint: ', 'Format Expectation:\n');

    for (let rowIdx = 2; rowIdx <= 1000; rowIdx++) {
      columns.forEach((_, colIdx) => {
        const cell = sheet.getCell(rowIdx, colIdx + 1);
        cell.protection = { locked: false };
      });

      sheet.getCell(`A${rowIdx}`).numFmt = '@'; 
      sheet.getCell(`B${rowIdx}`).numFmt = '@';
      
      sheet.getCell(`C${rowIdx}`).dataValidation = { type: 'list', allowBlank: true, formulae: [`Hidden_Lists!$A$1:$A$${locations.length}`] };
      sheet.getCell(`D${rowIdx}`).dataValidation = { type: 'list', allowBlank: true, formulae: [`Hidden_Lists!$B$1:$B$${statuses.length}`] };
      sheet.getCell(`E${rowIdx}`).dataValidation = { type: 'list', allowBlank: true, formulae: [`Hidden_Lists!$C$1:$C$${institutes.length}`] };
    }

    await sheet.protect('atlas_db_protection', { selectLockedCells: true, selectUnlockedCells: true, formatColumns: true, formatRows: true });

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `${selectedType.replace(/\s+/g, '_')}_Template.xlsx`;
    link.click();
    URL.revokeObjectURL(url);
  };

  const handleFileUpload = (e: any) => {
    const file = e.target.files[0];
    if (!file || !selectedType) return;
    const reader = new FileReader();
    reader.onload = async (ev) => {
      const buffer = ev.target?.result;
      if (!buffer) return;
      try {
        const workbook = new ExcelJS.Workbook();
        await workbook.xlsx.load(buffer as ArrayBuffer);
        const worksheet = workbook.getWorksheet('Template') || workbook.worksheets[0];
        const headers = worksheet.getRow(1).values as string[];
        const parsedData: any[] = [];

        worksheet.eachRow((row, rowNumber) => {
          if (rowNumber === 1) return; 
          const rowData: any = { type_name: selectedType };
          row.eachCell({ includeEmpty: true }, (cell, colNumber) => {
            const headerKey = headers[colNumber];
            if (headerKey) {
              let val = cell.value;
              if (val && typeof val === 'object' && 'result' in val) val = val.result;
              if (val && typeof val === 'object' && 'text' in val) val = val.text;
              
              const rawStr = val !== undefined && val !== null ? String(val).trim() : '';
              if (headerKey === 'id' && selectedTypeObj?.id_regex) {
                rowData[headerKey] = applyAtlasBarcodeMask(rawStr, selectedTypeObj.id_regex);
              } else {
                rowData[headerKey] = rawStr;
              }
            }
          });
          if (rowData.id && !rowData.serial) rowData.serial = rowData.id;
          if (rowData.serial && !rowData.id) rowData.id = rowData.serial;
          if (rowData.id || rowData.serial) parsedData.push(rowData);
        });

        setStagingData(prev => [...prev, ...parsedData]);
        setValidationResult(null);
        setForceCommitWarnings(false);
        toast.info(`${parsedData.length} rows loaded into Staging Area.`);
      } catch (err) {
        toast.error("Failed to parse Excel. Ensure it is the provided template.");
      }
    };
    reader.readAsArrayBuffer(file);
    if (fileInputRef.current) fileInputRef.current.value = '';
  };

  const generateRange = () => {
    if (!selectedType) return;
    const batch: EquipmentRow[] = [];
    for (let i = rangeParams.start; i <= rangeParams.end; i++) {
      const generatedId = `${rangeParams.prefix}${i.toString().padStart(rangeParams.padding, '0')}${rangeParams.suffix}`;
      batch.push({ 
        id: generatedId, 
        serial: copyIdToSerial ? generatedId : '',
        type_name: selectedType, 
        ...commonFields 
      });
    }
    setStagingData(prev => [...prev, ...batch]);
    setValidationResult(null);
    setForceCommitWarnings(false);
    toast.info(`Appended ${batch.length} items to Staging Area.`);
  };

  const runValidation = () => {
    if (stagingData.length === 0) {
      toast.warn("Staging area is empty.");
      return;
    }
    setIsValidating(true);
    setForceCommitWarnings(false);
    
    const enrichedData = prepareDataForBackend(stagingData);
    
    validateMutation.mutate({ equipments: enrichedData }, {
      onSuccess: (res: any) => {
        let parsedData = res?.data ?? res;

        if (typeof parsedData === 'string') {
            try {
                const match = parsedData.match(/\{[\s\S]*\}/); 
                if (match) parsedData = JSON.parse(match[0]);
                else parsedData = JSON.parse(parsedData);
            } catch (e) {
                console.error("Failed to parse server string:", parsedData);
            }
        }

        if (parsedData && Array.isArray(parsedData.details)) {
          processValidationPayload(parsedData);
        } else {
          const debugStr = typeof parsedData === 'object' ? JSON.stringify(parsedData) : String(parsedData);
          toast.error(`Falha: Resposta inesperada. Recebido: ${debugStr.substring(0, 150)}`);
          setIsValidating(false);
        }
      },
      onError: (error: any) => {
        let parsedData = error.response?.data;

        if (typeof parsedData === 'string') {
            try {
                const match = parsedData.match(/\{[\s\S]*\}/);
                if (match) parsedData = JSON.parse(match[0]);
            } catch(e) {}
        }

        if (parsedData && Array.isArray(parsedData.details)) {
            processValidationPayload(parsedData);
            return;
        }

        const errMsg = error.response?.data?.messages?.error || error.message || "Erro desconhecido";
        toast.error(`Falha no Pedido: ${errMsg}`);
        setIsValidating(false);
      }
    });
  };

  const processValidationPayload = (backendData: any) => {
    let globalHasWarnings = false;

    const apiDetails = backendData.details || stagingData.map((_, i) => ({ rowIndex: i, valid: false, errors: { system: "Payload Error" } }));

    const enrichedDetails = apiDetails.map((rowStatus: any) => {
      const row = stagingData[rowStatus.rowIndex];
      if (!row) return rowStatus; 

      const warnings: Record<string, string> = {};
      rowStatus.errors = rowStatus.errors || {};

      if (row.location_name && !locations.includes(row.location_name)) {
        rowStatus.valid = false;
        rowStatus.errors['location_name'] = `'${row.location_name}' is invalid.`;
      }
      if (row.status_name && !statuses.includes(row.status_name)) {
        rowStatus.valid = false;
        rowStatus.errors['status_name'] = `'${row.status_name}' is invalid.`;
      }
      if (row.owner_name && !institutes.includes(row.owner_name)) {
        rowStatus.valid = false;
        rowStatus.errors['owner_name'] = `'${row.owner_name}' is invalid.`;
      }

      if (rowStatus.valid && currentRegex && row.id && !currentRegex.test(row.id)) {
        warnings['id'] = `ID '${row.id}' deviates from usual format.`;
        globalHasWarnings = true;
      }

      return { ...rowStatus, warnings };
    });

    const finalValid = enrichedDetails.every((d:any) => d.valid);
    setValidationResult({ valid: finalValid, hasWarnings: globalHasWarnings, details: enrichedDetails });

    if (finalValid && !globalHasWarnings) {
       toast.success("All records valid and ready to commit!");
    } else if (finalValid && globalHasWarnings) {
       toast.warn("Data valid, but review ID pattern warnings.");
    } else {
       const firstErrorRow = enrichedDetails.find((d: any) => !d.valid);
       const firstErrorMsg = firstErrorRow ? Object.values(firstErrorRow.errors || {})[0] : "Check rows highlighted in red.";
       toast.error(`Validation Failed (Row ${firstErrorRow?.rowIndex + 1}): ${firstErrorMsg}`);
    }
    
    setIsValidating(false);
  };

  const commitBatch = () => {
    setIsSubmitting(true);
    const enrichedData = prepareDataForBackend(stagingData);

    commitMutation.mutate({ equipments: enrichedData }, {
      onSuccess: () => {
        toast.success("Batch registration completed successfully!");
        setStagingData([]);
        setValidationResult(null);
        setIsSubmitting(false);
        setForceCommitWarnings(false);
        recentEquipQuery.refetch();
      },
      onError: (error: any) => {
        const errMsg = error.response?.data?.messages?.error || error.response?.data?.message || "An error occurred during submission.";
        toast.error(`Submit failed: ${errMsg}`);
        setIsSubmitting(false);
      }
    });
  };

  const onCellEditComplete = (e: any) => {
    let { rowData, newValue, field, originalEvent: event } = e;
    if (field === 'location_name' && newValue && !locations.includes(newValue)) event.preventDefault();
    else if (field === 'status_name' && newValue && !statuses.includes(newValue)) event.preventDefault();
    else if (field === 'owner_name' && newValue && !institutes.includes(newValue)) event.preventDefault();
    else {
      if (field === 'id' && selectedTypeObj?.id_regex) {
        rowData[field] = applyAtlasBarcodeMask(String(newValue), selectedTypeObj.id_regex);
      } else {
        rowData[field] = newValue;
      }
      setValidationResult(null);
    }
  };

  const textEditor = (options: ColumnEditorOptions) => {
    return <InputText type="text" value={options.value || ''} onChange={(e: any) => options.editorCallback!(e.target.value)} onKeyDown={(e) => e.stopPropagation()} />;
  };

  const dropdownEditor = (options: ColumnEditorOptions, dataList: string[]) => {
    return <Dropdown value={options.value} options={dataList} onChange={(e: any) => options.editorCallback!(e.value)} placeholder="Select" className="w-full" />;
  };

  if (equipTypesQuery.isPending) return <p>Loading system components...</p>;

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
      <div className="mb-4">
        <label className="font-bold block mb-2 text-700">1. Select Equipment Category</label>
        <Dropdown value={selectedType} options={equipTypesQuery.data?.data?.map((t:any)=>({label:t.name, value:t.name})) || []} 
          onChange={(e) => {setSelectedType(e.value);}} 
          placeholder="Choose the Equipment Type" className="w-full md:w-30rem" />
        {selectedType && <Message severity="info" text={smartHint} className="mt-3 w-full justify-content-start" />}
      </div>

      <TabView className="mb-4">
        <TabPanel header="Method A: Excel Upload" disabled={!selectedType}>
          <div className="flex flex-column gap-3">
            <p className="m-0 text-600">The template includes locked headers, dropdown menus, and validation rules for <b>{selectedType}</b>.</p>
            <div className="flex gap-3">
              <Button label="1. Download Excel Template" icon="pi pi-file-excel" severity="info" outlined onClick={downloadExcelTemplate} type="button" />
              <input type="file" accept=".xlsx" ref={fileInputRef} onChange={handleFileUpload} className="hidden" id="excel-upload" />
              <Button label="2. Upload Filled Excel" icon="pi pi-upload" severity="secondary" onClick={()=>document.getElementById('excel-upload')?.click()} type="button" />
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
            <div className="mt-2 flex align-items-center">
              <Checkbox inputId="copySerial" checked={copyIdToSerial} onChange={e => setCopyIdToSerial(e.checked || false)} />
              <label htmlFor="copySerial" className="ml-2 text-sm text-600">Automatically copy ID to Serial Number</label>
            </div>
          </div>

          <h4 className="mb-2 text-700">Replicated Data (Applied to all items)</h4>
          <div className="grid p-fluid">
            <div className="col-12 md:col-3"><label className="text-sm">Location</label><Dropdown value={commonFields['location_name']} options={locations} onChange={(e)=>setCommonFields({...commonFields, location_name: e.value})} showClear placeholder="Select Location" /></div>
            <div className="col-12 md:col-3"><label className="text-sm">Status</label><Dropdown value={commonFields['status_name']} options={statuses} onChange={(e)=>setCommonFields({...commonFields, status_name: e.value})} showClear placeholder="Select Status" /></div>
            <div className="col-12 md:col-3"><label className="text-sm">Owner Institute</label><Dropdown value={commonFields['owner_name']} options={institutes} onChange={(e)=>setCommonFields({...commonFields, owner_name: e.value})} showClear placeholder="Select Institute" /></div>
            <div className="col-12 md:col-3"><label className="text-sm">Comments</label><InputText value={commonFields['comments'] || ''} onChange={(e)=>setCommonFields({...commonFields, comments: e.target.value})} /></div>

            {dynamicParams.map(field => (
              <div className="col-12 md:col-3" key={field}><label className="text-sm capitalize text-blue-600 font-semibold">{snakeToSpaced(field)}</label><InputText value={commonFields[field] || ''} onChange={(e)=>setCommonFields({...commonFields, [field]: e.target.value})} /></div>
            ))}
            
            <div className="col-12 flex justify-content-end mt-2">
              <Button label="Add to Staging Area" icon="pi pi-plus" onClick={generateRange} type="button" severity="secondary" />
            </div>
          </div>
        </TabPanel>
      </TabView>

      {stagingData.length > 0 && (
        <div className="mt-5 border-top-1 border-300 pt-4">
          <div className="flex justify-content-between align-items-center mb-3">
            <div>
              <h3 className="m-0 text-800">2. Staging Area: Verify & Edit Data</h3>
              <span className="text-500 text-sm">Click on any cell to edit. Re-run validation after changes.</span>
            </div>
            <Button label="Run Validation" icon="pi pi-shield" severity="warning" onClick={runValidation} loading={isValidating} type="button" />
          </div>

          <DataTable value={stagingData} editMode="cell" showGridlines size="small" scrollHeight="400px" scrollable rowClassName={getRowClass}>
            
            <Column header="Status" body={(_, options) => {
              const res = validationResult?.details[options.rowIndex];
              if (!res) return <i className="pi pi-circle text-gray-300" title="Pending validation" />;
              
              if (!res.valid) {
                const errorMsg = Object.entries(res.errors || {}).map(([k, v]) => `${snakeToSpaced(k)}: ${v}`).join('\n');
                return <i className="pi pi-times-circle text-red-600 text-xl cursor-help" title={`Errors:\n${errorMsg}`} />;
              }
              if (Object.keys(res.warnings).length > 0) {
                const warnMsg = Object.entries(res.warnings).map(([k, v]) => `${snakeToSpaced(k)}: ${v}`).join('\n');
                return <i className="pi pi-exclamation-triangle text-yellow-600 text-xl cursor-help" title={`Warnings:\n${warnMsg}`} />;
              }
              return <i className="pi pi-check-circle text-green-600 text-xl" title="Perfectly Valid" />;
            }} style={{width: '5rem', textAlign: 'center'}} />
            
            <Column field="id" header="ID / Barcode" editor={(options) => textEditor(options)} onCellEditComplete={onCellEditComplete} style={{ minWidth: '150px' }} />
            <Column field="serial" header="Serial No." editor={(options) => textEditor(options)} onCellEditComplete={onCellEditComplete} style={{ minWidth: '150px' }} />
            <Column field="location_name" header="Location" editor={(options) => dropdownEditor(options, locations)} onCellEditComplete={onCellEditComplete} style={{ minWidth: '150px' }} />
            <Column field="status_name" header="Status" editor={(options) => dropdownEditor(options, statuses)} onCellEditComplete={onCellEditComplete} style={{ minWidth: '150px' }} />
            <Column field="owner_name" header="Owner" editor={(options) => dropdownEditor(options, institutes)} onCellEditComplete={onCellEditComplete} style={{ minWidth: '150px' }} />
            <Column field="comments" header="Comments" editor={(options) => textEditor(options)} onCellEditComplete={onCellEditComplete} style={{ minWidth: '200px' }} />
            
            {dynamicParams.map(col => (
              <Column key={col} field={col} header={snakeToSpaced(col)} editor={(options) => textEditor(options)} onCellEditComplete={onCellEditComplete} style={{ minWidth: '120px' }} />
            ))}
          </DataTable>

          <div className="mt-4 flex flex-column align-items-end gap-3">
            {validationResult?.valid && validationResult?.hasWarnings && (
               <div className="flex align-items-center">
                  <Checkbox inputId="forceAck" checked={forceCommitWarnings} onChange={e => setForceCommitWarnings(e.checked || false)} />
                  <label htmlFor="forceAck" className="ml-2 text-yellow-700 font-bold">I confirm I want to store records that deviate from standard patterns.</label>
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