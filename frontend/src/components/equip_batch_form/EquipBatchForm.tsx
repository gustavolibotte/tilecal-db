import { useMutation, useQuery } from '@tanstack/react-query';
import './equip_batch_form.css';
import { useFormik } from 'formik';
import FormDropdown from '../form_dropdown/FormDropdown';
import FormFile from '../form_file/FormFile';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { AxiosError } from 'axios';
import { ReactNode, useState } from 'react';
import { toast } from 'react-toastify';
import { snakeToSpaced } from '../../utils/string';
import { DataInfo, DataInfoFieldMeta } from '../data_info/DataInfo';
import { Button } from 'primereact/button';
import { isObjEmpty } from '../../utils/objects';

interface EquipBatchFormSchema {
  type_name: string;
  file: File | string;
}

export default function EquipBatchForm() {

  const [equipFieldErrors, setEquipFieldErrors] = useState({});
  const [errorMessage, setErrorMessage] = useState('');

  const equipTypesQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', {}]
  });

  const initialValues = {
    type_name: '', file: ''
  };

  const batchMutation = useMutation({
    mutationFn: makeMutationFunction(
      'post', 
      getEndpoint('equipment_batch')),
    onMutate: () => {
      setEquipFieldErrors({});
      setErrorMessage('');
      toast.info('Submitting data', {
        autoClose: false,
        hideProgressBar:true
      });
    },
    onSuccess: () => {
      toast.dismiss();
      toast.success('Equipment registered with success');
      formik.resetForm();
    },
    onError: (error: AxiosError) => {
      // The following "?" fields are guaranteed with the app
      toast.dismiss();
      const responseData: any = error?.response?.data;
      if (responseData.error == __INVALID_DATA_MSG__){
        Object.keys(responseData.messages).forEach((key: string) => {
          formik.setFieldError(key as keyof EquipBatchFormSchema, responseData.messages[key]);
        });
      } else if (responseData.error.includes('entry')){
        setEquipFieldErrors(responseData.messages);
        setErrorMessage(responseData.error);
      } else{
        toast.error(__DEFAULT_ERROR_MSG__);
        console.log(error);
      }
    },
  });

  const submitData = async (data: EquipBatchFormSchema) => {
    const formData = new FormData();
    formData.append('type_name', data.type_name);
    formData.append('file', data.file);
    batchMutation.mutate(formData);
  };

  const formik = useFormik({
    initialValues: initialValues,
    onSubmit: submitData
  });

  if (equipTypesQuery.isError){
    return __DEFAULT_ERROR_MSG__;
  } else if (equipTypesQuery.isPending){
    return 'Loading';
  }

  const equipTypeOptions = equipTypesQuery.data.data.map((equipType: any) => {
    return {
      name: equipType.name,
      value: equipType.name
    };
  }); 

  const getFormErrorMessage = (name: keyof EquipBatchFormSchema): string | null => {
      return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };

  const showFieldErrors = (): ReactNode => {
    if (isObjEmpty(equipFieldErrors) && errorMessage){
      return <></>;
    }
    const fieldConfig: DataInfoFieldMeta[] = [];
    Object.keys(equipFieldErrors).forEach((key: string) => {
      fieldConfig.push({name: snakeToSpaced(key), field: key});
    })
    return (
      <div className='mt-3 text-red-500'>
        <h3>{errorMessage}</h3>
        <DataInfo
          data={equipFieldErrors}
          fields={fieldConfig}/>
      </div>
    );
  };

  return (
    <div className="card flex justify-content-center flex-column gap-2">
      <form onSubmit={formik.handleSubmit}>
        {showFieldErrors()}
        <div className='mt-3'>
          <FormDropdown
            id='type_name'
            name='Type Name'
            options={equipTypeOptions}
            required
            value={formik.values.type_name}
            help='Equipment type to submit.'
            onChange={(e) => {
              formik.setFieldValue('type_name', e.value);
            }}
            errorText={getFormErrorMessage('type_name')}/>
          </div>
        <div className='mt-3'>
          <FormFile
            id='file'
            name='Equipment data'
            help={`File to upload (50kB max size)`}
            onChange={(event: any) => {
              formik.setFieldValue("file", event.currentTarget.files[0]);
            }}
            errorText={getFormErrorMessage('file')} />
        </div>
        <div className='mt-3'><Button type="submit" label="Submit" icon="pi pi-file-import"/></div>
      </form>
    </div>
  )
}
