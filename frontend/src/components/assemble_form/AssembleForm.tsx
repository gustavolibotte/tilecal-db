import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import './assemble_form.css';
import { defaultQueryFunction, getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { toast, Id } from 'react-toastify';
import { useFormik } from 'formik';
import { AxiosError } from 'axios';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import UpgradePositionInput from './UpgradePositionInput';
import { drawerTypes, scanId, scanPosition } from '../../utils/assemblyScan';
import SlotSearchInput from '../slot_search_input/SlotSearchInput';
import { ReactNode, useEffect, useRef, useState } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';
import { preventEnterDefault } from '../../utils/event';
import { extractSequentialNumber } from '../../utils/validation_rules';

interface AssembleFormValues {
  [key: string]: string;
}

interface AssemblyPayload {
  id: string;
  upgrade_resolution?: { pmt_id: string; source: string; version: string };
  slots: Array<{ name: string; equipment_id: string; upgrade_position?: number; position_version?: string }>;
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

  // IDs become valid only after a successful server check.
  const [verifiedSlots, setVerifiedSlots] = useState<Record<string, boolean>>({});

  const isDrawer = drawerTypes.includes(parentTypeName);
  const needsPosition = (slot: string) => isDrawer && slot.includes('pmt_block_slot');
  const [positions, setPositions] = useState<Record<string, string>>({});
  const [validPositions, setValidPositions] = useState<Record<string, boolean>>({});
  const [validationEpoch, setValidationEpoch] = useState(0);
  // State for Pre-commit Popup
  const [selectedEquipments, setSelectedEquipments] = useState<Record<string, any>>({});
  const selectedRef = useRef(selectedEquipments); selectedRef.current = selectedEquipments;
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
          filledSlots.some(([name]) => !verifiedSlots[name] || (needsPosition(name) && !validPositions[name])) || isSubmitDisabled) return;
      setEquipSlotErrors([]);
      setPositionConflict(null); setPositionSource('');
      const mutationData: AssemblyPayload = {
        id: extractSequentialNumber(parentId),
        slots: filledSlots
          .map(([name, id]) => ({
            name,
            equipment_id: scanId(id)!,
            ...(needsPosition(name) ? { upgrade_position: scanPosition(positions[name] || '')!,
              position_version: selectedEquipments[name]?.positions?.version } : {})
          }))
      };

      // Instead of mutating immediately, store payload and open the Pre-commit Dialog
      setMutationDataPayload(mutationData);
      setShowConfirmDialog(true);
    },
  });

  useEffect(() => {
    formik.resetForm(); setVerifiedSlots({}); setPositions({}); setValidPositions({});
    setSelectedEquipments({}); selectedRef.current = {};
    setShowConfirmDialog(false); setMutationDataPayload(null); setEquipSlotErrors([]);
    setPositionConflict(null); setPositionSource('');
  }, [parentId, parentTypeName]);

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
      setVerifiedSlots({}); setPositions({}); setValidPositions({});
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
      setVerifiedSlots({}); setValidationEpoch(epoch => epoch + 1);
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

  const filledSlotsCount = Object.values(formik.values).filter(value => value.trim()).length;

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

  const requiredSlots = isDrawer ? sortedSlots : sortedSlots.filter(slot => formik.values[slot.name]?.trim());
  const isSubmitDisabled = assembleMutation.isPending || filledSlotsCount === 0 || requiredSlots.some(slot =>
    !verifiedSlots[slot.name] || (needsPosition(slot.name) && !validPositions[slot.name]));

  const executeAssembly = () => {
    if (savingRef.current || isSubmitDisabled || !mutationDataPayload) return;
    // Save only the configuration that is still displayed and verified.
    const currentSlots = Object.entries(formik.values).filter(([, id]) => id.trim() !== '');
    if (mutationDataPayload.id !== extractSequentialNumber(parentId) ||
        mutationDataPayload.slots.length !== currentSlots.length ||
        mutationDataPayload.slots.some(slot => !verifiedSlots[slot.name] ||
          slot.equipment_id !== scanId(formik.values[slot.name] || '') ||
          (needsPosition(slot.name) && slot.upgrade_position !== scanPosition(positions[slot.name] || '')))) return;
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
                    {isDrawer && <th className="p-2">Upgrade Position</th>}
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
                            {isDrawer && <td className="p-2">{slot.upgrade_position !== undefined && <>
                              {equip?.positions?.box_position ?? 'Not recorded'} → <b>{slot.upgrade_position}</b>
                            </>}</td>}
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


          return (
            <div className="mt-4 mb-2 p-3 border-1 surface-border border-round" key={slot.name}>
              <SlotSearchInput
                id={slot.name}
                autoFocus={slot.name === sortedSlots[0]?.name}
                label={label}
                value={formik.values[slot.name] || ''}
                onChange={(val: string) => {
                  formik.setFieldValue(slot.name, val);
                  // Any edit invalidates the checked identity and the preview.
                  if (val !== formik.values[slot.name]) {
                    setMutationDataPayload(null);
                    setShowConfirmDialog(false);
                    setVerifiedSlots(prev => ({...prev, [slot.name]: false}));
                    setPositions(prev => ({...prev, [slot.name]: ''}));
                    setValidPositions(prev => ({...prev, [slot.name]: false}));
                    setSelectedEquipments(prev => {
                      const newObj = {...prev};
                      delete newObj[slot.name];
                      return newObj;
                    });
                  }
                }}
                validationEpoch={validationEpoch}
                validateEquipment={async (id: string) => {
                  const response: any = await defaultQueryFunction({ queryKey: ['assembly_scan', 'get', {
                    parent_id: scanId(parentId), equipment_id: id, slot: slot.name
                  }] });
                  const equipment = response.data?.[0];
                  if (!equipment || equipment.id !== id) throw new Error('Invalid validation response. Try again.');
                  if (Object.entries(selectedRef.current).some(([name, item]) => name !== slot.name && item?.id === id)) {
                    throw new Error('This equipment is already selected in another slot.');
                  }
                  return equipment;
                }}
                onEquipmentSelect={(equip: any) => {
                  selectedRef.current = {...selectedRef.current, [slot.name]: equip};
                  setSelectedEquipments(selectedRef.current);
                  setVerifiedSlots(prev => ({...prev, [slot.name]: !!equip}));
                }}
                allowedTypes={allowedTypes}
              />

              {needsPosition(slot.name) && <UpgradePositionInput
                id={`${slot.name}-position`} value={positions[slot.name] || ''}
                disabled={!verifiedSlots[slot.name]}
                onChange={value => {
                  setPositions(prev => ({...prev, [slot.name]: value}));
                  setMutationDataPayload(null); setShowConfirmDialog(false);
                }}
                onValidated={valid => setValidPositions(prev => ({...prev, [slot.name]: valid}))}
              />}

            </div>
          );
        })}

        <div className="mt-4 flex flex-column">
          <Button
            type="submit" data-assembly-field
            label="Confirm Assembly"
            icon="pi pi-box"
            disabled={isSubmitDisabled}
            className={isSubmitDisabled ? 'p-button-secondary' : 'p-button-primary'}
          />
          {isSubmitDisabled && filledSlotsCount > 0 && (
             <small className="text-orange-500 mt-2 font-bold">
               <i className="pi pi-lock mr-1"></i>
               Validate all required equipment IDs and positions to confirm assembly.
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
