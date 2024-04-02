import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import './assemble_form.css';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { toast, Id } from 'react-toastify';
import { useFormik } from 'formik';
import { AxiosError } from 'axios';
import { Button } from 'primereact/button';
import FormText from '../form_text/FormText';
import { snakeToSpaced } from '../../utils/string';
import { useRef } from 'react';
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

export default function AssembleForm({ parentId, parentTypeName, barcodeReaderSafe }: AssembleFormProps) {

  const initialValues: any = {};

  const equipTypeQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', {type_name: parentTypeName}]
  });

  const submitData = (data: AssembleFormSchema) => {
    const mutationData: any = {
      id: extractSequentialNumber(parentId),
      slots: []
    };
    //Adds slots from the form
    Object.entries(data).forEach(([name, id]) => {
      if (id.length){
        mutationData.slots.push({
          name: name,
          equpment_id: extractSequentialNumber(id)
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
    },
    onError: (error: AxiosError) => {
      // The following "?" field types are guaranteed with the app architecture
      console.log(error)
      toast.dismiss(toastId.current)
      const responseData: any = error?.response?.data;
      console.log(responseData);
      if (responseData.message == __INVALID_DATA_MSG__) {
        toast.error(__INVALID_DATA_MSG__)
        Object.keys(responseData.data).forEach((key: string) => {
          formik.setFieldError(key, responseData.data[key]);
        });
      } else {
        toast.error(__DEFAULT_ERROR_MSG__);
        console.log(error);
      }
    },
  });

  const getFormErrorMessage = (name: keyof AssembleFormSchema): string | null => {
    return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };
  
  if (equipTypeQuery.isError){
    return <div className="card flex justify-content-center flex-column gap-2">
      <h4>{__DEFAULT_ERROR_MSG__}</h4>
    </div>;
  } else if(equipTypeQuery.isPending){
    return <div className="card flex justify-content-center flex-column gap-2">
      <h4>Loading</h4>
    </div>;
  }

  const equipTypeData = equipTypeQuery.data.data[0];
  if (!equipTypeData.slot_names){
    return <div className="card flex justify-content-center flex-column gap-2">
      <h4>This equipment doesn't have slots to assemble.</h4>
    </div>;
  }
  const equipSlotFields: Array<any> = [];
  equipTypeData.slot_names.forEach((slotName: string) => {
    const spacedName = snakeToSpaced(slotName);
    const field =  (
      <div className='mt-3'>
        <FormText
          id={slotName}
          name={spacedName}
          value={formik.values[slotName]}
          help={`Equipment to be assembled into ${spacedName}`}
          required
          onChange={(e) => {
            formik.setFieldValue(slotName, e.target.value);
          }}
          errorText={getFormErrorMessage(slotName)} />
      </div>);
    equipSlotFields.push(field)
    });
  const onKeyDown = barcodeReaderSafe? preventEnterDefault: undefined;
  return (
    <div className="card flex justify-content-center flex-column gap-2">
    <form onSubmit={formik.handleSubmit} onKeyDown={onKeyDown}>
      {equipSlotFields}
      <div className='mt-3'><Button type="submit" label="Assemble"/></div>
    </form>
  </div>
  )
}
