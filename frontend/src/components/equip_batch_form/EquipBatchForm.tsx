import { useMutation, useQuery } from '@tanstack/react-query';
import './equip_batch_form.css';
import { DEFAULT_ERROR_MSG } from '../../utils/constants';
import { useFormik } from 'formik';
import FormDropdown from '../form_dropdown/FormDropdown';
import FormFile from '../form_file/FormFile';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { AxiosError } from 'axios';
import { ProgressBar } from 'primereact/progressbar';
import { useState } from 'react';

interface EquipBatchFormSchema {
  type_name: string;
  file?: File;
}

export default function EquipBatchForm() {

  const [progress, setProgress] = useState(0);

  const equipTypesQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', {}]
  });

  const initialValues = {
    type_name: '', file: undefined
  };

  const equipMutation = useMutation({
    mutationFn: makeMutationFunction(
      'post', 
      getEndpoint('equipment')),
    onMutate: () => {
    },
    onSuccess: () => {
    },
    onError: (error: AxiosError) => {
      // The following "?" fields are guaranteed with the app
    },
  });

  const submitData = () => {

  };

  const formik = useFormik({
    initialValues: initialValues,
    onSubmit: submitData
  });

  if (equipTypesQuery.isError){
    return DEFAULT_ERROR_MSG;
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

  const submitionComponent = () => {
    <ProgressBar value={progress}></ProgressBar>
  };

  return (
    <div className="card flex justify-content-center flex-column gap-2">
      <form onSubmit={formik.handleSubmit}>
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
      </form>
    </div>
  )
}
