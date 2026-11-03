import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import './assemble_form.css';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { toast, Id } from 'react-toastify';
import { useFormik } from 'formik';
import { AxiosError } from 'axios';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { Checkbox } from 'primereact/checkbox';
import SlotSearchInput from '../slot_search_input/SlotSearchInput';
import { ReactNode, useRef, useState } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';
import { preventEnterDefault } from '../../utils/event';
import { extractSequentialNumber } from '../../utils/validation_rules';

interface AssembleFormValues {
  [key: string]: string;
}

interface AssemblyPayload {
  id: string;
  upgrade_resolution?: { pmt_id: string; source: string; version: string };
  slots: Array<{ name: string; equipment_id: string }>;
}

interface AssembleFormProps {
  parentId: string;
  parentTypeName: string;
  barcodeReaderSafe?: boolean;
}

const formatSlotLabel = (slotName: string): string => {
  const name = String(slotName).toLowerCase();

  if (name.includes('electronics_slot') || name.includes('fenics')) return 'FENICS';
  if (name.includes('hv_divider_slot')) return 'Active HV Divider';
  if (name.includes('pmt_slot')) return 'PMT';

  if (name.includes('mainboard_slot')) return 'Mainboard';
  if (name.includes('daughterboard_slot')) return 'Daughterboard';

  if (name.includes('pmt_block_slot')) {
    const match = name.match(/\d+$/);
    return match ? `PMT Block ${match[0]}` : 'PMT Block';
  }

  return String(slotName)
    .replace(/pmt_block_|mini_drawer_|_slot/g, '')
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (l) => l.toUpperCase());
};

export default function AssembleForm({ parentId, parentTypeName, barcodeReaderSafe }: AssembleFormProps) {
  const [equipSlotErrors, setEquipSlotErrors] = useState<Array<ReactNode>>([]);
  
  // State for Mandatory Position Double-Check
  const [verifiedSlots, setVerifiedSlots] = useState<Record<string, boolean>>({});
  
  // State for Pre-commit Popup
  const [selectedEquipments, setSelectedEquipments] = useState<Record<string, any>>({});
  const [showConfirmDialog, setShowConfirmDialog] = useState(false);
  const [mutationDataPayload, setMutationDataPayload] = useState<AssemblyPayload | null>(null);

  const [positionConflict, setPositionConflict] = useState<any>(null);
  const [positionSource, setPositionSource] = useState('');
  const queryClient = useQueryClient();
  const toastId = useRef<Id>('');
  const savingRef = useRef(false);

  const equipTypeQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', { type_name: parentTypeName }]
  });

  const formik = useFormik<AssembleFormValues>({
    initialValues: {},
    onSubmit: (values) => {
      const filledSlots = Object.entries(values).filter(([, id]) => id.trim() !== '');
      if (savingRef.current || filledSlots.length === 0 ||
          filledSlots.some(([name]) => !verifiedSlots[name])) return;
      setEquipSlotErrors([]);
      setPositionConflict(null); setPositionSource('');
      const mutationData: AssemblyPayload = {
        id: extractSequentialNumber(parentId),
        slots: filledSlots
          .map(([name, id]) => ({
            name,
            equipment_id: extractSequentialNumber(id.trim())
          }))
      };
      
      // Instead of mutating immediately, store payload and open the Pre-commit Dialog
      setMutationDataPayload(mutationData);
      setShowConfirmDialog(true);
    },
  });

  const assembleMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment_assemble')),
    onMutate: () => { savingRef.current = true; toastId.current = toast.info('Assembling...', midTransactionToastConfig); },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      toast.success('Assembled successfully');
      setShowConfirmDialog(false);
      setMutationDataPayload(null);
      setPositionConflict(null); setPositionSource('');
      queryClient.invalidateQueries({ queryKey: ['pmt_positions'] });
      queryClient.invalidateQueries({ queryKey: ['equipment'] });
      queryClient.invalidateQueries({ queryKey: ['equipment_hist'] });
      formik.resetForm();
      setVerifiedSlots({});
      setSelectedEquipments({});
      queryClient.invalidateQueries({ queryKey: ['equipment', 'get', { relatedEquipmentId: parentId }] });
      setEquipSlotErrors([]);
    },
    onError: (error: AxiosError) => {
      toast.dismiss(toastId.current);
      const resp: any = error?.response?.data;
      if (error.response?.status === 409 && resp?.data?.code === 'upgrade_position_conflict') {
        setPositionConflict(resp.data); setPositionSource(''); setShowConfirmDialog(true);
        return;
      }
      setShowConfirmDialog(false);
      setMutationDataPayload(null);
      if (resp?.message === __INVALID_DATA_MSG__ && resp.data) {
        setEquipSlotErrors(Object.values(resp.data).map((v: any, i) => <p key={i}>{v}</p>));
      } else if (error.response?.status === 409) {
        toast.error(resp?.message || 'Assembly changed. Reload and try again.');
      } else {
        toast.error(error.response?.status === 500
          ? 'Assembly failed on the server (HTTP 500). Check the backend logs.'
          : __DEFAULT_ERROR_MSG__);
      }
    },
    onSettled: () => { savingRef.current = false; }
  });

  // Calculate if the submit button should be locked
  const filledSlotsCount = Object.values(formik.values).filter(val => val && String(val).trim() !== '').length;
  const verifiedCount = Object.keys(formik.values).filter(key => {
    const val = formik.values[key];
    return val && String(val).trim() !== '' && verifiedSlots[key];
  }).length;
  
  // Submit is disabled if NO slots are filled, or if the number of filled slots DOES NOT MATCH the verified checkboxes
  const isSubmitDisabled = assembleMutation.isPending || filledSlotsCount === 0 || filledSlotsCount !== verifiedCount;

  if (equipTypeQuery.isPending) return <h4>Loading assembly slots...</h4>;
  if (equipTypeQuery.isError) return <h4>Error loading equipment metadata.</h4>;

  const responseData = equipTypeQuery.data?.data || [];
  const typeData = Array.isArray(responseData) ? responseData[0] : responseData.data?.[0];

  let slotsToRender: any[] = [];
  if (typeData && typeData.slot_names) {
    const namesArray = Array.isArray(typeData.slot_names)
      ? typeData.slot_names
      : String(typeData.slot_names).split(',');
    slotsToRender = namesArray.filter((n: string) => n.trim().length > 0).map((n: string) => ({ name: n.trim() }));
  }

  if (slotsToRender.length === 0) {
    return (
      <div className="card p-3 text-yellow-600 bg-yellow-50 border-round">
        <i className="pi pi-info-circle mr-2"></i>
        Note: <b>{parentTypeName}</b> is a standalone component and does not receive sub-assemblies.
      </div>
    );
  }

  const sortedSlots = [...slotsToRender].sort((a, b) => a.name.localeCompare(b.name));

  const executeAssembly = () => {
    if (savingRef.current || isSubmitDisabled || !mutationDataPayload) return;
    // Save only the configuration that is still displayed and verified.
    const currentSlots = Object.entries(formik.values).filter(([, id]) => id.trim() !== '');
    if (mutationDataPayload.id !== extractSequentialNumber(parentId) ||
        mutationDataPayload.slots.length !== currentSlots.length ||
        mutationDataPayload.slots.some(slot => !verifiedSlots[slot.name] ||
          slot.equipment_id !== extractSequentialNumber((formik.values[slot.name] || '').trim()))) return;
    if (positionConflict && !positionSource) return;
    savingRef.current = true;
    assembleMutation.mutate({ ...mutationDataPayload,
      ...(positionConflict ? { upgrade_resolution: { pmt_id: positionConflict.pmt_id,
        source: positionSource, version: positionConflict.version } } : {}) });
  };

  const renderConfirmationTable = () => {
    if (!mutationDataPayload || !mutationDataPayload.slots) return null;
    
    return (
        <table className="w-full text-left border-collapse mt-3">
            <thead>
                <tr className="border-bottom-1 surface-border">
                    <th className="p-2">Slot Name</th>
                    <th className="p-2">Equipment ID</th>
                    <th className="p-2">Component Specifications</th>
                </tr>
            </thead>
            <tbody>
                {mutationDataPayload.slots.map((slot: any) => {
                    const equip = selectedEquipments[slot.name];
                    const typeName = equip?.type_name || 'N/A';
                    
                    // Identify cable specifications for visual highlighting
                    const isShortCable = typeName.includes('Short Cable') || typeName === 'PMT Block' || typeName === 'PMT Block (Standard)';
                    const isLongCable = typeName.includes('Long Cable');
                    
                    return (
                        <tr key={slot.name} className="border-bottom-1 surface-border">
                            <td className="p-2">{formatSlotLabel(slot.name)}</td>
                            <td className="p-2 font-bold">{slot.equipment_id}</td>
                            <td className="p-2">
                                {typeName}
                                {isShortCable && <span className="ml-2 px-2 py-1 bg-orange-100 text-orange-800 border-round font-bold text-xs">SHORT CABLE</span>}
                                {isLongCable && <span className="ml-2 px-2 py-1 bg-teal-100 text-teal-800 border-round font-bold text-xs">LONG CABLE</span>}
                            </td>
                        </tr>
                    );
                })}
            </tbody>
        </table>
    );
  };

  return (
    <div className="card flex flex-column gap-3">
      {equipSlotErrors.length > 0 && <div className="p-message p-message-error mb-3">{equipSlotErrors}</div>}
      
      <form onSubmit={formik.handleSubmit} onKeyDown={barcodeReaderSafe ? preventEnterDefault : undefined}>
        <fieldset disabled={assembleMutation.isPending} style={{ border: 0, padding: 0, margin: 0 }}>
        {sortedSlots.map((slot: any) => {
          const label = formatSlotLabel(slot.name);
          let allowedTypes: string[] | undefined = undefined;
          
          try {
            if (typeData.slots) {
              const parsedSlots = typeof typeData.slots === 'string' ? JSON.parse(typeData.slots) : typeData.slots;
              const slotDef = parsedSlots.find((s: any) => s.name === slot.name);
              if (slotDef && slotDef.allowed_type_ids) {
                allowedTypes = slotDef.allowed_type_ids;
              }
            }
          } catch (e) {
            console.warn("Could not parse allowed types for slot", slot.name);
          }

          const isFilled = formik.values[slot.name] && String(formik.values[slot.name]).trim() !== '';

          return (
            <div className="mt-4 mb-2 p-3 border-1 surface-border border-round" key={slot.name}>
              <SlotSearchInput
                id={slot.name}
                label={label}
                value={formik.values[slot.name] || ''}
                onChange={(val: string) => {
                  formik.setFieldValue(slot.name, val);
                  // Any ID edit invalidates the physical check and the preview.
                  if (val !== formik.values[slot.name]) {
                    setMutationDataPayload(null);
                    setShowConfirmDialog(false);
                    setVerifiedSlots(prev => ({...prev, [slot.name]: false}));
                    setSelectedEquipments(prev => {
                      const newObj = {...prev};
                      delete newObj[slot.name];
                      return newObj;
                    });
                  }
                }}
                onEquipmentSelect={(equip: any) => {
                  setSelectedEquipments(prev => ({...prev, [slot.name]: equip}));
                  // Reset verification when changing equipment to force a new check
                  setVerifiedSlots(prev => ({...prev, [slot.name]: false}));
                }}
                allowedTypes={allowedTypes}
              />
              
              {/* Mandatory Position Double-Check Checkbox */}
              {isFilled && (
                <div className="flex align-items-center mt-3 ml-1">
                  <Checkbox 
                      disabled={assembleMutation.isPending}
                      inputId={`verify_${slot.name}`} 
                      checked={!!verifiedSlots[slot.name]} 
                      onChange={(e) => setVerifiedSlots(prev => ({...prev, [slot.name]: e.checked || false}))} 
                  />
                  <label htmlFor={`verify_${slot.name}`} className={`ml-2 text-sm font-bold ${verifiedSlots[slot.name] ? 'text-green-600' : 'text-orange-500'}`}>
                      {verifiedSlots[slot.name] ? '✓ Physically verified' : 'Required: Confirm physical position in slot'}
                  </label>
                </div>
              )}
            </div>
          );
        })}
        
        <div className="mt-4 flex flex-column">
          <Button 
            type="submit" 
            label="Confirm Assembly" 
            icon="pi pi-box" 
            disabled={isSubmitDisabled}
            className={isSubmitDisabled ? 'p-button-secondary' : 'p-button-primary'}
          />
          {isSubmitDisabled && filledSlotsCount > 0 && (
             <small className="text-orange-500 mt-2 font-bold">
               <i className="pi pi-lock mr-1"></i>
               Please check the physical verification box for all selected equipments to unlock assembly.
             </small>
          )}
        </div>
        </fieldset>
      </form>

      {/* Pre-commit Validation Popup */}
      <Dialog 
          header="Confirm Assembly Configuration" 
          visible={showConfirmDialog} 
          style={{ width: '60vw' }} 
          breakpoints={{ '960px': '85vw', '640px': '100vw' }}
          closable={!assembleMutation.isPending}
          closeOnEscape={!assembleMutation.isPending}
          onHide={() => { if (!savingRef.current) { setShowConfirmDialog(false); setMutationDataPayload(null); } }}
          footer={
              <div className="flex justify-content-end gap-2 mt-4">
                  <Button type="button" disabled={assembleMutation.isPending} label="Cancel / Edit" icon="pi pi-times" onClick={() => { setShowConfirmDialog(false); setMutationDataPayload(null); }} className="p-button-text p-button-secondary" />
                  <Button type="button" loading={assembleMutation.isPending} disabled={isSubmitDisabled || !mutationDataPayload || (!!positionConflict && !positionSource)} label="Save Assembly" icon="pi pi-check" onClick={executeAssembly} autoFocus className="p-button-success" />
              </div>
          }
      >
          <div className="mb-2">
              <i className="pi pi-exclamation-triangle text-orange-500 mr-2 text-xl align-middle"></i>
              <span className="text-lg">Please double-check the configuration before saving to the database.</span>
          </div>
          {renderConfirmationTable()}
          {positionConflict && <fieldset className="mt-3 p-3" disabled={assembleMutation.isPending}>
            <legend>Choose Upgrade Position</legend>
            <p>{positionConflict.changed ? 'Positions changed. Review the current values and choose again.' : 'Block and PMT have different Upgrade Positions.'}</p>
            <p>The selected position will be saved on both when assembly succeeds.</p>
            {(['block', 'pmt'] as const).map(source => <div className="field flex align-items-center gap-2" key={source}>
              <input type="radio" name="upgrade-resolution" id={`upgrade-${source}`} value={source}
                checked={positionSource === source} onChange={() => setPositionSource(source)} />
              <label htmlFor={`upgrade-${source}`}>
                {source === 'block' ? `Block ${positionConflict.block_id}` : `PMT ${positionConflict.pmt_id}`}:
                {' '}{(source === 'block' ? positionConflict.block_position : positionConflict.pmt_position) ?? 'Not recorded'}
              </label>
            </div>)}
          </fieldset>}
      </Dialog>
    </div>
  );
}
