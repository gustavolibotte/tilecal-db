import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useRef } from 'react';
import { Id, toast } from 'react-toastify';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { midTransactionToastConfig } from '../../utils/toast';
import { AxiosError } from 'axios';
import { useFormik } from 'formik';
import * as Yup from 'yup';
import { equipIdRules, fileNameRules } from '../../utils/validation_rules';
import { preventEnterDefault } from '../../utils/event';
import { Button } from 'primereact/button';
import FormText from '../form_text/FormText';

interface RelateFileFormSchema {
  file_name: string;
  equipment_id: string;
}

interface RelateFileFormProps {
  equipmentId?: string;
}

export default function RelateFileForm({ equipmentId }: RelateFileFormProps) {

  const initialValues: RelateFileFormSchema = {
    file_name: '',
    equipment_id: equipmentId? equipmentId: ''
  };

  const queryClient = useQueryClient();
  const toastId = useRef<Id>();

  const relationMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('file_relation')),
    onMutate: () => {
      toastId.current = toast.info('Relating file to equipment.', midTransactionToastConfig);
    },
    onSuccess: () => {
      if (toastId.current) {
        toast.update(toastId.current, {
          render: 'File related to equipment.',
          type: 'success',
          autoClose: 3000
        });
      }
    },
    onError: (error: AxiosError) => {
      // The following "?" fields are guaranteed with the app
      const responseData: any = error?.response?.data;
      if (responseData.message == __INVALID_DATA_MSG__) {
        Object.keys(responseData.data).forEach((key: string) => {
          formik.setFieldError(key as keyof RelateFileFormSchema, responseData.data[key]);
        });
      } else {
        if (toastId.current) {
          toast.update(toastId.current, {
            render: __DEFAULT_ERROR_MSG__,
            type: 'error',
            autoClose: 5000
          });
        }
        console.log(error);
      }
    },
    onSettled: () => {
      // Invalidates files cache
      queryClient.invalidateQueries({
        queryKey: ['file', 'get']
      });
    }
  })

  const submitData = (data: RelateFileFormSchema) => {
    relationMutation.mutate(data);
  }

  const validationSchema = {
    file_name: fileNameRules.required(__FIELD_REQUIRED_MSG__),
    equipment_id: equipIdRules.required(__FIELD_REQUIRED_MSG__)

  }

  const formik = useFormik({
    initialValues: initialValues,
    onSubmit: submitData,
    validationSchema: Yup.object(validationSchema)
  });

  const getFormErrorMessage = (name: keyof RelateFileFormSchema): string | null => {
    return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };

  let equipmentIdField;
  if (equipmentId){
    equipmentIdField = <FormText
      id='equipment_id'
      name='Equipment ID'
      value={formik.values.equipment_id}
      help='Equipment ID (Field is frozen)'
      onChange={(e) => {}}
      errorText={getFormErrorMessage('equipment_id')} />
  } else {
    equipmentIdField = <FormText
      id='equipment_id'
      name='Equipment ID'
      value={formik.values.equipment_id}
      help='Equipment ID (Use the barcode)'
      onChange={(e) => {
        formik.setFieldValue('equipment_id', e.target.value);
      }}
      errorText={getFormErrorMessage('equipment_id')} />
  }


  return (
    <div className="card flex justify-content-center flex-column gap-2">
      <form onSubmit={formik.handleSubmit} onKeyDown={preventEnterDefault}>
        <div className='mt-3'>
          {equipmentIdField}
        </div>
        <div className='mt-3'>
          <FormText
            id='file_name'
            name='File Name'
            value={formik.values.file_name}
            help='Name of the file ot relate to the equipment'
            onChange={(e) => {
              formik.setFieldValue('file_name', e.target.value);
            }}
            errorText={getFormErrorMessage('file_name')} />
        </div>
        <div className='mt-3'><Button type="submit" label="Submit" /></div>
      </form>
    </div>
  )
}
