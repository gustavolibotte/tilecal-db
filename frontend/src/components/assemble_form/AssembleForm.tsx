import { useMutation, useQuery } from '@tanstack/react-query';
import './assemble_form.css';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { toast } from 'react-toastify';
import { useFormik } from 'formik';
import { AxiosError } from 'axios';
import { Button } from 'primereact/button';
import FormText from '../form_text/FormText';
import FormDropdown from '../form_dropdown/FormDropdown';
import { snakeToSpaced } from '../../utils/string';

interface AssembleFormSchema{
    child_id: string;
    slot_name: string;
    parent_id?: string;
}

interface AssembleFormProps{
  parentId: string;
  parentTypeName: string;
}

export default function AssembleForm({ parentId, parentTypeName }: AssembleFormProps) {

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

  const assembleMutation = useMutation({
    mutationFn: makeMutationFunction(
      'post',
      getEndpoint('equipment_assemble')),
    onMutate: () => {
      toast.info('Assembling equipment.', {
        autoClose: false,
        hideProgressBar:true
      });
    },
    onSuccess: () => {
      toast.dismiss();
      toast.success('Equipment assembled with success');
      formik.resetForm();
    },
    onError: (error: AxiosError) => {
      // The following "?" fields are guaranteed with the app
      toast.dismiss();
      const responseData: any = error?.response?.data;
      if (responseData.error == __INVALID_DATA_MSG__) {
        Object.keys(responseData.messages).forEach((key: string) => {
          formik.setFieldError(key as keyof AssembleFormSchema, responseData.messages[key]);
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
        name: snakeToSpaced(slotName),
        value: slotName
      }});
  } else{
    return <div className="card flex justify-content-center flex-column gap-2">
      <h4>This equipment doesn't have slots to assemble.</h4>
    </div>;
  }

  return (
    <div className="card flex justify-content-center flex-column gap-2">
    <form onSubmit={formik.handleSubmit}>
      <div className='mt-3'>
        <FormText
          id='child_id'
          name='ID to be assemble'
          value={formik.values.child_id}
          help='File name'
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
          help='Slot to fir the equipment'
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
