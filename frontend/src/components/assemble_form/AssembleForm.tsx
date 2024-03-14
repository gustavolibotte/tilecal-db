import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import './assemble_form.css';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { toast, Id } from 'react-toastify';
import { useFormik } from 'formik';
import { AxiosError } from 'axios';
import { Button } from 'primereact/button';
import FormText from '../form_text/FormText';
import FormDropdown from '../form_dropdown/FormDropdown';
import { snakeToSpaced } from '../../utils/string';
import { useRef } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';
import { preventEnterDefault } from '../../utils/event';

interface AssembleFormSchema{
    child_id: string;
    slot_name: string;
    parent_id?: string;
}

interface AssembleFormProps{
  parentId: string;
  parentTypeName: string;
  barcodeReaderSafe?: boolean;
}

export default function AssembleForm({ parentId, parentTypeName, barcodeReaderSafe }: AssembleFormProps) {

  const initialValues: AssembleFormSchema = {
    child_id: '', slot_name: ''
  };

  const equipTypeQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', {type_name: parentTypeName}]
  });

  const submitData = (data: AssembleFormSchema) => {
    data.parent_id = parentId;
    assembleMutation.mutate(data);
  }

  const formik = useFormik({
    initialValues: initialValues,
    onSubmit: submitData
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
      // The following "?" fields are guaranteed with the app
      console.log(error)
      toast.dismiss(toastId.current)
      const responseData: any = error?.response?.data;
      console.log(responseData);
      if (responseData.message == __INVALID_DATA_MSG__) {
        Object.keys(responseData.data).forEach((key: string) => {
          formik.setFieldError(key as keyof AssembleFormSchema, responseData.data[key]);
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
  let equipSlotOptions;
  if (equipTypeData.slot_names){
    equipSlotOptions = equipTypeData.slot_names.map((slotName: string) => {
      return {
        name: snakeToSpaced(slotName? slotName: ''),
        value: slotName
      }});
  } else{
    return <div className="card flex justify-content-center flex-column gap-2">
      <h4>This equipment doesn't have slots to assemble.</h4>
    </div>;
  }

  const onKeyDown = barcodeReaderSafe? preventEnterDefault: undefined;
  return (
    <div className="card flex justify-content-center flex-column gap-2">
    <form onSubmit={formik.handleSubmit} onKeyDown={onKeyDown}>
      <div className='mt-3'>
        <FormText
          id='child_id'
          name='Equipment to be assembled'
          value={formik.values.child_id}
          help='Equipment to be assembled into the selected one. Example: AB1234'
          required
          onChange={(e) => {
            formik.setFieldValue('child_id', e.target.value);
          }}
          errorText={getFormErrorMessage('child_id')} />
      </div>
      <div className='mt-3'>
        <FormDropdown
          id='slot_name'
          name='Slot'
          options={equipSlotOptions}
          required
          value={formik.values.slot_name}
          help='Slot to install the equipment'
          onChange={(e) => {
            formik.setFieldValue('slot_name', e.value);
          }}
          errorText={getFormErrorMessage('slot_name')}/>
      </div>
      <div className='mt-3'><Button type="submit" label="Assemble"/></div>
    </form>
  </div>
  )
}
