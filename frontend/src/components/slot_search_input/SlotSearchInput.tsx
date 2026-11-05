import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { InputText } from 'primereact/inputtext';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { defaultQueryFunction } from '../../utils/DbClient';
import { focusNextScanField, scanId } from '../../utils/assemblyScan';

interface SlotSearchInputProps {
  id: string;
  autoFocus?: boolean;
  label: string;
  value: string;
  onChange: (value: string) => void;
  onEquipmentSelect?: (equipment: any) => void;
  validateEquipment?: (id: string) => Promise<any>;
  validationEpoch?: number;
  allowedTypes?: string[];
  errorText?: string | null;
  required?: boolean;
  help?: string;
}

export default function SlotSearchInput(props: SlotSearchInputProps) {
  const { id, label, value, onChange, allowedTypes, errorText, required, help } = props;
  const [open, setOpen] = useState(false);
  const [results, setResults] = useState<any[]>([]);
  const [searching, setSearching] = useState(false);
  const [status, setStatus] = useState<'idle' | 'pending' | 'valid' | 'invalid'>('idle');
  const [message, setMessage] = useState('');
  const [focusVersion, setFocusVersion] = useState(0);
  useLayoutEffect(() => { if (focusVersion) focusNextScanField(id); }, [focusVersion]);
  const generation = useRef(0);
  const timer = useRef<ReturnType<typeof setTimeout>>();
  const latest = useRef(props); latest.current = props;
  const accepted = useRef('');
  const mounted = useRef(true);
  useEffect(() => { mounted.current = true; return () => {
    mounted.current = false; generation.current++; clearTimeout(timer.current);
  }; }, []);

  const validate = async (raw: string, advance = true) => {
    clearTimeout(timer.current);
    const canonical = scanId(raw);
    const request = ++generation.current;
    accepted.current = '';
    latest.current.onEquipmentSelect?.(null);
    if (!canonical) {
      setStatus('invalid'); setMessage('Enter a complete equipment ID.'); return;
    }
    setStatus('pending'); setMessage('');
    try {
      let equipment;
      if (latest.current.validateEquipment) equipment = await latest.current.validateEquipment(canonical);
      else {
        const response: any = await defaultQueryFunction({ queryKey: ['equipment', 'get', { id: canonical, id_matchMode: 'equals' }] });
        const rows = Array.isArray(response.data) ? response.data : response.data?.data || [];
        equipment = rows.find((row: any) => row.id === canonical);
        if (!equipment) throw new Error('Equipment is not registered in the database.');
      }
      if (!mounted.current || request !== generation.current) return;
      accepted.current = canonical;
      onChange(canonical);
      latest.current.onEquipmentSelect?.(equipment);
      setStatus('valid'); setMessage('');
      if (advance) setFocusVersion(version => version + 1);
    } catch (error: any) {
      if (!mounted.current || request !== generation.current) return;
      const response = error.response;
      setStatus('invalid');
      setMessage(response ? (response.data?.message || 'Could not validate equipment. Try again.')
        : error.message || 'Could not validate equipment. Check the connection.');
      // Keep the operator in the failed field; do not steal focus after an intentional click elsewhere.
      const input = document.getElementById(id) as HTMLInputElement | null;
      if (document.activeElement === input) input?.select();
    }
  };

  useEffect(() => {
    clearTimeout(timer.current); generation.current++;
    if (value === accepted.current && value) return;
    accepted.current = ''; setStatus('idle'); setMessage('');
    if (value.trim()) timer.current = setTimeout(() => validate(value), 400);
    return () => clearTimeout(timer.current);
  }, [value]);

  useEffect(() => {
    if (!props.validationEpoch) return;
    accepted.current = ''; generation.current++; setStatus('idle');
    latest.current.onEquipmentSelect?.(null);
    if (scanId(latest.current.value)) void validate(latest.current.value, false);
  }, [props.validationEpoch]);

  const search = async () => {
    clearTimeout(timer.current); generation.current++;
    setSearching(true); setOpen(true);
    try {
      const params: any = { id: value.trim(), id_matchMode: 'contains' };
      if (allowedTypes?.length) params.type_id = allowedTypes;
      const response: any = await defaultQueryFunction({ queryKey: ['equipment', 'get', params] });
      setResults(Array.isArray(response.data) ? response.data : response.data?.data || []);
    } catch { setResults([]); setMessage('Search failed. Try again.'); }
    finally { setSearching(false); }
  };

  return <div className="field">
    <label htmlFor={id} className="font-bold">{label}{required && '*'}:</label>
    <div className="p-inputgroup mt-1">
      <InputText id={id} autoFocus={props.autoFocus} data-assembly-field value={value} autoComplete="off"
        aria-invalid={status === 'invalid'} aria-describedby={`${id}-feedback`}
        onChange={event => {
          generation.current++; accepted.current = ''; clearTimeout(timer.current);
          setStatus('idle'); setMessage(''); onChange(event.target.value);
          latest.current.onEquipmentSelect?.(null);
        }}
        onKeyDown={event => {
          if (event.key === 'Enter' || (event.key === 'Tab' && !event.shiftKey)) {
            event.preventDefault(); event.stopPropagation(); void validate(event.currentTarget.value);
          }
        }} placeholder="Scan ID or type and search..." className={status === 'invalid' || errorText ? 'p-invalid' : ''} />
      <span className="p-inputgroup-addon" role="status" aria-label={status}>
        {status === 'pending' && <i className="pi pi-spin pi-spinner" />}
        {status === 'valid' && <i className="pi pi-check text-green-600" />}
        {status === 'invalid' && <i className="pi pi-times text-red-600" />}
      </span>
      <Button icon="pi pi-search" type="button" aria-label={`Search ${label}`} onClick={search} loading={searching} />
    </div>
    <small id={`${id}-feedback`} role="status" className="p-error block mt-1">{message || errorText}</small>
    {help && <small className="block mt-1 text-500">{help}</small>}
    <Dialog header={`Select Equipment for: ${label}`} visible={open} style={{ width: '70vw' }} onHide={() => setOpen(false)}>
      <DataTable value={results} loading={searching} selectionMode="single"
        onRowSelect={event => { setOpen(false); onChange(event.data.id);
          requestAnimationFrame(() => { document.getElementById(id)?.focus(); void validate(event.data.id); }); }}
        emptyMessage="No equipment found." paginator rows={5} stripedRows showGridlines>
        <Column field="id" header="ID" /><Column field="type_name" header="Type" />
        <Column field="status_name" header="Status" /><Column field="location_name" header="Location" />
      </DataTable>
    </Dialog>
  </div>;
}
