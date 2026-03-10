import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import './assemble_form.css';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { toast, Id } from 'react-toastify';
import { useFormik } from 'formik';
import { AxiosError } from 'axios';
import { Button } from 'primereact/button';
import FormText from '../form_text/FormText';
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
          .filter(([_, id]) => id.length > 0)
          .map(([name, id]) => ({
            name,
            equipment_id: extractSequentialNumber(id)
          }))
      };
      assembleMutation.mutate(mutationData);
    },
  });

  const assembleMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment_assemble')),
    onMutate: () => { toastId.current = toast.info('Assembling...', midTransactionToastConfig); },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      toast.success('Assembled successfully');
      formik.resetForm();
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

  return (
    <div className="card flex flex-column gap-3">
      {equipSlotErrors.length > 0 && <div className="p-message p-message-error">{equipSlotErrors}</div>}
      <form onSubmit={formik.handleSubmit} onKeyDown={barcodeReaderSafe ? preventEnterDefault : undefined}>
        {sortedSlots.map((slot: any) => {
          const label = formatSlotLabel(slot.name);
          return (
            <div className="field mt-4" key={slot.name}>
              <FormText
                id={slot.name}
                name={label}
                value={formik.values[slot.name] || ''}
                onChange={(e: any) => formik.setFieldValue(slot.name, e.target.value)}
                placeholder={`Scan ID for ${label}`}
                help=""
                errorText=""
              />
            </div>
          );
        })}
        <div className="mt-4">
          <Button type="submit" label="Confirm Assembly" icon="pi pi-box" />
        </div>
      </form>
    </div>
  );
}