import { useFormik } from 'formik';
import { Id, toast } from 'react-toastify';
import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import FormText from '../form_text/FormText';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import FormTextArea from '../form_text_area/FormTextArea';
import { Button } from 'primereact/button';
import * as Yup from 'yup';

import './file_form.css';
import { useRef } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';
import { fileDescriptionRules, fileNameRules, filePathRules } from '../../utils/validation_rules';

interface FileFormSchema {
  name: string;
  path: string;
  description: string;
}

interface FileFormProps {
  fileId?: string;
  onSuccess?: () => void;
}

export default function FileForm({ fileId, onSuccess }: FileFormProps) {

  const isEdit = fileId? true: false;
  const initialValues: FileFormSchema = {
    name: '', description: '', path: ''
  };

  const toastId = useRef<Id>();

  const fileMutation = useMutation({
    mutationFn: makeMutationFunction(
      'post',
      fileId? getEndpoint('file_put'): getEndpoint('file')),
    onMutate: () => {
      toastId.current = toast.info('Uploading file.', midTransactionToastConfig);
    },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      if (fileId){
        toast.success('File edited with success');
      } else{
        toast.success('File registered with success');
      }
      if (onSuccess){
        onSuccess();
      }
      formik.resetForm();
    },
    onError: (error: AxiosError) => {
      toast.dismiss(toastId.current);
      // The following "?" fields are guaranteed with the app
      const responseData: any = error?.response?.data;
      console.log(error)
      if (responseData.message == __INVALID_DATA_MSG__) {
        Object.keys(responseData.data).forEach((key: string) => {
          formik.setFieldError(key as keyof FileFormSchema, responseData.data[key]);
        });
      } else {
        toast.error(__DEFAULT_ERROR_MSG__);
      }
    },
  });

  const submitData = async (data: any) => {
    console.log('SUBMITTING');
    console.log(data);
    if (fileId){
      data.id = fileId;
    }
    fileMutation.mutate(data);
  }

  const validationSchema = {
    name: fileNameRules,
    path: filePathRules,
    description: fileDescriptionRules
  };
  if (!isEdit){
    validationSchema.name = validationSchema.name.required(__FIELD_REQUIRED_MSG__);
    validationSchema.path = validationSchema.path.required(__FIELD_REQUIRED_MSG__);
    validationSchema.description = validationSchema.description.required(__FIELD_REQUIRED_MSG__);
  }

  const formik = useFormik({
    initialValues: initialValues,
    onSubmit: submitData,
    validationSchema: Yup.object(validationSchema)
  });

  const getFormErrorMessage = (name: keyof FileFormSchema): string | null => {
    return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };

  return (
    <div className="card flex justify-content-center flex-column gap-2">
      <form onSubmit={formik.handleSubmit}>
        <div className='mt-3'>
          <FormText
            id='name'
            name='Name'
            value={formik.values.name}
            help='File name'
            required={!isEdit}
            onChange={(e) => {
              formik.setFieldValue('name', e.target.value);
            }}
            errorText={getFormErrorMessage('name')}
            placeholder='filename.png' />
        </div>
        <div className='mt-3'>
          <FormText
            id='path'
            name='Path'
            value={formik.values.path}
            help='CERNBox link to file location'
            required={!isEdit}
            onChange={(e) => {
              formik.setFieldValue('path', e.target.value);
            }}
            errorText={getFormErrorMessage('path')}
            placeholder='https://cernbox.cern.ch/public-link-to-file-or-directory' />
        </div>
        <div className='mt-3'>
          <FormTextArea
            id='description'
            name='Description'
            value={formik.values.description}
            help='Max size of 60000 characters'
            placeholder='Brief summary of file content.'
            required={!isEdit}
            onChange={(e) => {
              formik.setFieldValue('description', e.target.value);
            }}
            errorText={getFormErrorMessage('description')} />
        </div>
        <div className='mt-3'><Button type="submit" label="Submit"/></div>
      </form>
    </div>
  )
}
