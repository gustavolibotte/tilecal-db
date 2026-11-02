import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Button } from 'primereact/button';
import { defaultQueryFunction, getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { useUser } from '../../utils/user';

type Field = 'phase1_position' | 'box_position';
const fields: { name: Field; label: string; min: number; max: number }[] = [
  { name: 'phase1_position', label: 'Phase1 Position', min: 1, max: 49 },
  { name: 'box_position', label: 'Box Position', min: 0, max: 48 },
];
const display = (value: unknown) => value === null || value === undefined || value === '' ? 'Not recorded' : String(value);
const editable = (value: unknown) => value === null || value === undefined ? '' : String(value);

export default function PmtPositions({ equipmentId }: { equipmentId: string }) {
  const user = useUser();
  const client = useQueryClient();
  const query = useQuery({ queryKey: ['pmt_positions', 'get', { equipment_id: equipmentId }],
    queryFn: defaultQueryFunction, refetchInterval: 15000, refetchOnWindowFocus: 'always' });
  const [draft, setDraft] = useState<Record<Field, string> | null>(null);
  const [original, setOriginal] = useState<any>(null);
  const [message, setMessage] = useState('');
  const [conflict, setConflict] = useState(false);
  const current = query.data?.data?.[0];
  const save = useMutation({ mutationFn: makeMutationFunction('post', getEndpoint('pmt_positions')),
    onSuccess: async () => {
      setDraft(null); setMessage('Positions saved on the PMT.');
      await Promise.all(['pmt_positions', 'equipment', 'equipment_hist'].map(key => client.invalidateQueries({ queryKey: [key] })));
    },
    onError: (error: any) => {
      setConflict(error.response?.status === 409);
      setMessage(error.response?.status === 409 ? 'PMT data or assembly changed. Your draft is retained. Copy it if needed, then cancel and reload.'
        : 'Could not save. Your draft is retained. Check the connection and retry.');
    },
  });
  const shown = draft ? original : current;
  const changedAssembly = !!draft && !!current && (current.pmt_id !== original.pmt_id || current.version !== original.version);
  return <section className="mt-4" aria-label="PMT position traceability">
    <h3>PMT position traceability</h3>
    <p>These attributes belong to the PMT. Phase1 Position records its pre-upgrade position; Box Position records the position on the transport box. They are independent of the block's current Detector Location.</p>
    {query.isPending && <p>Loading PMT positions...</p>}
    {query.isError && <p role="alert">Could not resolve PMT positions. Check the assembly and retry. <Button label="Reload" onClick={() => query.refetch()} /></p>}
    {shown && !shown.pmt_id && <p>No PMT is mounted in this block. Mount a PMT to view its positions.</p>}
    {shown?.pmt_id && <>
      <p><b>{equipmentId === shown.pmt_id ? 'PMT: ' : 'Mounted PMT: '}</b>
        <a href={`${__APP_BASEURL__}/equipment/${encodeURIComponent(shown.pmt_id)}`}>{shown.pmt_id}</a>.
        {equipmentId !== shown.pmt_id && ' Values are read from this PMT, not stored on this block.'}</p>
      {fields.map(field => <div className="field" key={field.name}>
        <label htmlFor={`${equipmentId}_${field.name}`} className="font-bold">{field.label}: </label>
        {draft ? <select id={`${equipmentId}_${field.name}`} value={draft[field.name]} disabled={save.isPending}
          onChange={e => setDraft({ ...draft, [field.name]: e.target.value })}>
          <option value="">Not recorded</option>
          {draft[field.name] !== '' && !Array.from({ length: field.max - field.min + 1 }, (_, i) => String(i + field.min)).includes(draft[field.name]) &&
            <option value={draft[field.name]}>{draft[field.name]} (existing value)</option>}
          {Array.from({ length: field.max - field.min + 1 }, (_, i) => i + field.min).map(n => <option key={n} value={n}>{n}</option>)}
        </select> : <span>{display(shown[field.name])}</span>}
      </div>)}
      {shown.phase1_position != null && shown.box_position != null && String(shown.phase1_position) !== String(shown.box_position) &&
        <p>Recorded positions differ. Check the records and box marking; neither value is changed automatically.</p>}
      {draft ? <>
        <p>Saving updates PMT <b>{original.pmt_id}</b>, including its equipment history. Clearing a field explicitly removes its recorded value.</p>
        {(changedAssembly || conflict) && <p role="alert">The record changed. Cancel and reload before saving.</p>}
        <div className="flex gap-2">
          <Button label="Save PMT positions" loading={save.isPending} disabled={save.isPending || conflict || changedAssembly || query.isError}
            onClick={() => {
              const changes: Partial<Record<Field, number | null>> = {};
              for (const field of fields) if (draft[field.name] !== editable(original[field.name])) {
                const value = draft[field.name] === '' ? null : Number(draft[field.name]);
                if (value !== null && (!Number.isInteger(value) || value < field.min || value > field.max)) {
                  setMessage(`Invalid ${field.label}.`); return;
                }
                changes[field.name] = value;
              }
              if (!Object.keys(changes).length) { setDraft(null); setMessage('No changes to save.'); return; }
              save.mutate({ equipment_id: equipmentId, pmt_id: original.pmt_id, version: original.version, changes });
            }} />
          <Button label="Cancel / Reload" disabled={save.isPending} onClick={() => { setDraft(null); setConflict(false); setMessage(''); query.refetch(); }} />
        </div>
      </> : user.canEdit() && <Button label="Edit PMT positions" disabled={query.isError || query.isFetching}
        onClick={() => { setOriginal(current); setDraft({ phase1_position: editable(current.phase1_position), box_position: editable(current.box_position) }); setConflict(false); setMessage(''); }} />}
    </>}
    {message && <p role="status">{message}</p>}
  </section>;
}
