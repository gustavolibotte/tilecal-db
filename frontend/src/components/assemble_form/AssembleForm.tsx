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
  
  const [verifiedSlots, setVerifiedSlots] = useState<Record<string, boolean>>({});
  
  const [showConfirmDialog, setShowConfirmDialog] = useState(false);
  const [mutationDataPayload, setMutationDataPayload] = useState<any>(null);

  const queryClient = useQueryClient();
  const toastId = useRef<Id>('');

  const equipTypeQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', { type_name: parentTypeName }]
  });

  const formik = useFormik<AssembleFormValues>({
    initialValues: {},
    onSubmit: (values) => {
      const mutationData = {
        id: extractSequentialNumber(parentId),
        slots: (Object.entries(values) as [string, string][])
          .filter(([_, id]) => id && id.length > 0)
          .map(([name, id]) => ({
            name,
            equipment_id: extractSequentialNumber(id)
          }))
      };
      
      setMutationDataPayload(mutationData);
      setShowConfirmDialog(true);
    },
  });

  const assembleMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment_assemble')),
    onMutate: () => { toastId.current = toast.info('Assembling...', midTransactionToastConfig); },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      toast.success('Assembled successfully');
      formik.resetForm();
      setVerifiedSlots({});
      queryClient.invalidateQueries({ queryKey: ['equipment', 'get', { relatedEquipmentId: parentId }] });
      setEquipSlotErrors([]);
    },
    onError: (error: AxiosError) => {
      toast.dismiss(toastId.current);
      const resp: any = error?.response?.data;
      if (resp?.message === __INVALID_DATA_MSG__) {
        setEquipSlotErrors(Object.values(resp.data).map((v: any, i) => <p key={i}>{v}</p>));
      } else {
        toast.error(__DEFAULT_ERROR_MSG__);
      }
    }
  });

  const filledSlotsCount = Object.values(formik.values).filter(val => val && val.trim().length > 0).length;
  const verifiedCount = Object.keys(formik.values).filter(key => {
    const val = formik.values[key];
    return val && val.trim().length > 0 && verifiedSlots[key];
  }).length;
  
  const isSubmitDisabled = filledSlotsCount === 0 || filledSlotsCount !== verifiedCount;

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
    setShowConfirmDialog(false);
    if (mutationDataPayload) {
      assembleMutation.mutate(mutationDataPayload);
    }
  };

  return (
    <div className="card flex flex-column gap-3">
      {equipSlotErrors.length > 0 && <div className="p-message p-message-error mb-3">{equipSlotErrors}</div>}
      
      <form onSubmit={formik.handleSubmit} onKeyDown={barcodeReaderSafe ? preventEnterDefault : undefined}>
        
        {sortedSlots.map((slot: any) => {
          const label = formatSlotLabel(slot.name);
          const isFilled = formik.values[slot.name] && formik.values[slot.name].trim().length > 0;
          
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
                label={label}
                value={formik.values[slot.name] || ''}
                onChange={(val: string) => {
                  formik.setFieldValue(slot.name, val);
                  if (!val) {
                    setVerifiedSlots(prev => ({...prev, [slot.name]: false}));
                  }
                }}
                allowedTypes={allowedTypes}
              />
              
              {isFilled && (
                <div className="flex align-items-center mt-3 ml-1">
                  <Checkbox 
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
      </form>

      <Dialog 
          header="Confirm Assembly Configuration" 
          visible={showConfirmDialog} 
          style={{ width: '60vw' }} 
          breakpoints={{ '960px': '85vw', '640px': '100vw' }}
          onHide={() => setShowConfirmDialog(false)}
          footer={
              <div className="flex justify-content-end gap-2 mt-4">
                  <Button label="Cancel / Edit" icon="pi pi-times" onClick={() => setShowConfirmDialog(false)} className="p-button-text p-button-secondary" />
                  <Button label="Save Assembly" icon="pi pi-check" onClick={executeAssembly} autoFocus className="p-button-success" />
              </div>
          }
      >
          <div className="mb-4">
              <i className="pi pi-exclamation-triangle text-orange-500 mr-2 text-xl align-middle"></i>
              <span className="text-lg">Please double-check the configuration before saving to the database.</span>
          </div>
          
          <table className="w-full text-left border-collapse mt-3">
              <thead>
                  <tr className="border-bottom-1 surface-border">
                      <th className="p-2">Slot Name</th>
                      <th className="p-2">Equipment ID</th>
                  </tr>
              </thead>
              <tbody>
                  {mutationDataPayload?.slots?.map((slot: any) => (
                      <tr key={slot.name} className="border-bottom-1 surface-border">
                          <td className="p-2">{formatSlotLabel(slot.name)}</td>
                          <td className="p-2 font-bold">{slot.equipment_id}</td>
                      </tr>
                  ))}
              </tbody>
          </table>
      </Dialog>
    </div>
  );
}