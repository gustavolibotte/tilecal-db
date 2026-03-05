import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import './assemble_form.css';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { toast, Id } from 'react-toastify';
import { useFormik } from 'formik';
import { AxiosError } from 'axios';
import { Button } from 'primereact/button';
import FormText from '../form_text/FormText';
import { snakeToSpaced } from '../../utils/string';
import { ReactNode, useRef, useState } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';
import { preventEnterDefault } from '../../utils/event';
import { extractSequentialNumber } from '../../utils/validation_rules';

interface AssembleFormSchema{
  [x: string | number | symbol]: string;
}

interface AssembleFormProps{
  parentId: string;
  parentTypeName: string;
  barcodeReaderSafe?: boolean;
}

const formatSlotLabel = (slotName: string): string => {
  const mapping: Record<string, string> = {
    'fenics': 'FENICS (Short Cable)',
    'fenics_short': 'FENICS (Short Cable)',
    'fenics_long': 'FENICS (Long Cable)',
    'pmt': 'PMT',
    'pmt_block': 'PMT Block',
    'active_hv_divider': 'Active HV Divider (Short Cable)',
    'active_hv_divider_short': 'Active HV Divider (Short Cable)',
    'active_hv_divider_long': 'Active HV Divider (Long Cable)',
    'mainboard': 'Mainboard',
    'daughterboard': 'Daughterboard',
    'hv_divider': 'HV Divider',
    'hv_micro': 'HV Micro',
    'adc': 'ADC',
    'dac': 'DAC'
  };
  
  return mapping[slotName.toLowerCase()] || snakeToSpaced(slotName);
};

export default function AssembleForm({ parentId, parentTypeName, barcodeReaderSafe }: AssembleFormProps) {

  const [equipSlotErrors, setEquipSlotErrors] = useState<Array<ReactNode>>([]);
  const initialValues: any = {};

  const equipTypeQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', {type_name: parentTypeName}]
  });

  const submitData = (data: AssembleFormSchema) => {
    const mutationData: any = {
      id: extractSequentialNumber(parentId),
      slots: []
    };
    Object.entries(data).forEach(([name, id]) => {
      if (id.length){
        mutationData.slots.push({
          name: name,
          equipment_id: extractSequentialNumber(id)
        });
      }
    })
    assembleMutation.mutate(mutationData);
  }

  const formik = useFormik({
    initialValues: initialValues,
    onSubmit: submitData,
  });

  const queryClient = useQueryClient();
  const toastId = useRef<Id>('');

  const assembleMutation = useMutation({
    mutationFn: makeMutationFunction(
      'post',
      getEndpoint('equipment_assemble')),
    onMutate: () => {
      toastId.current = toast.info('Assembling equipment.', midTransactionToastConfig);
    },
    onSuccess: () => {
      toast.dismiss(toastId.current)
      toast.success('Equipment assembled with success');
      formik.resetForm();
      queryClient.invalidateQueries({
        queryKey: [
          'equipment',
          'get',
          {relatedEquipmentId: parentId}
        ]
      });
      setEquipSlotErrors([]);
    },
    onError: (error: AxiosError) => {
      toast.dismiss(toastId.current)
      const responseData: any = error?.response?.data;
      if (responseData?.message === __INVALID_DATA_MSG__) {
        toast.error(__INVALID_DATA_MSG__)
        let errorTexts: Array<ReactNode> = [];
        Object.values(responseData.data).forEach((value: any, idx: number) => {
          errorTexts.push(
            <p key={idx}>{value}</p>
          );
        });
        setEquipSlotErrors(errorTexts);
      } else {
        toast.error(__DEFAULT_ERROR_MSG__);
      }
    },
  });
  
  if (equipTypeQuery.isError){
    return <div className="card flex justify-content-center flex-column gap-2">
      <h4>{__DEFAULT_ERROR_MSG__}</h4>
    </div>;
  } else if(equipTypeQuery.isPending){
    return <div className="card flex justify-content-center flex-column gap-2">
      <h4>Loading</h4>
    </div>;
  }

  const responseData: any = equipTypeQuery.data?.data || [];
  const equipTypeData = (!Array.isArray(responseData) && responseData.data !== undefined) 
    ? responseData.data[0] 
    : responseData[0];

  if (!equipTypeData || !equipTypeData.slot_names || equipTypeData.slot_names.length === 0){
    return (
      <div className="card flex justify-content-center flex-column gap-2 text-yellow-600">
        <h4>Note: This equipment type ({parentTypeName}) does not have predefined slots for assembly.</h4>
      </div>
    );
  }

  const sortedSlots = [...equipTypeData.slot_names].sort();
  
  const equipSlotFields: Array<any> = [];
  sortedSlots.forEach((slotName: string) => {
    const formattedName = formatSlotLabel(slotName);
    const field =  (
      <div className='mt-3' key={slotName}>
        <FormText
          id={slotName}
          name={formattedName}
          value={formik.values[slotName] || ''}
          help={`Equipment to be assembled into ${formattedName}`}
          onChange={(e) => {
            formik.setFieldValue(slotName, e.target.value);
          }}
          errorText='' />
      </div>);
    equipSlotFields.push(field)
    });

  const onKeyDown = barcodeReaderSafe? preventEnterDefault: undefined;
  
  return (
    <div className="card flex justify-content-center flex-column gap-2">
      {equipSlotErrors.length > 0 && <div className='text-red-500'>
        {equipSlotErrors}
      </div>}
    <form onSubmit={formik.handleSubmit} onKeyDown={onKeyDown}>
      {equipSlotFields}
      <div className='mt-3'><Button type="submit" label="Assemble"/></div>
    </form>
  </div>
  )
}