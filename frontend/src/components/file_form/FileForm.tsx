import { useFormik } from 'formik';
import { Id, toast } from 'react-toastify';
import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import FormText from '../form_text/FormText';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import FormTextArea from '../form_text_area/FormTextArea';
import { Button } from 'primereact/button';

import './file_form.css';
import FormFile from '../form_file/FormFile';
import { useRef } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';

interface FileFormSchema {
  name: string;
  file?: File;
  description: string;
}

interface FileFormProps {
  fileId?: string;
  onSuccess?: () => void;
}

export default function FileForm({ fileId, onSuccess }: FileFormProps) {

  const initialValues = {
    name: '', description: '', file: undefined
  };

  const headers = {
    'Content-Type': 'multipart/form-data'
  };

  const toastId = useRef<Id>();

  const fileMutation = useMutation({
    mutationFn: makeMutationFunction(
      'post',
      fileId? getEndpoint('file_put'): getEndpoint('file'),
      headers),
    onMutate: () => {
      toastId.current = toast.info('Uploading file.', midTransactionToastConfig);
    },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      if (fileId){
        toast.success('File edited with success');
      } else{
        toast.success('File uploaded with success');
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
      if (responseData.message == __INVALID_DATA_MSG__) {
        Object.keys(responseData.data).forEach((key: string) => {
          formik.setFieldError(key as keyof FileFormSchema, responseData.data[key]);
        });
      } else {
        toast.error(__DEFAULT_ERROR_MSG__);
        console.log(error);
      }
    },
  });

  const submitData = async (data: FileFormSchema) => {
    const formData = new FormData();
    if (data.file){
      formData.append('file', data.file);
    }
    if (fileId){
      formData.append('id', fileId);
    }
    formData.append('name', data.name);
    formData.append('description', data.description);
    fileMutation.mutate(formData);
  }

  const formik = useFormik({
    initialValues: initialValues,
    onSubmit: submitData
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
            required={fileId? false: true}
            onChange={(e) => {
              formik.setFieldValue('name', e.target.value);
            }}
            errorText={getFormErrorMessage('name')} />
        </div>
        <div className='mt-3'>
          <FormTextArea
            id='description'
            name='Description'
            value={formik.values.description}
            help='Max size of 60000 characters - Optional'
            placeholder='Brief summary of file content.'
            onChange={(e) => {
              formik.setFieldValue('description', e.target.value);
            }}
            errorText={getFormErrorMessage('description')} />
        </div>
        <div className='mt-3'>
          <FormFile
            id='file'
            name='File'
            help={`File to upload (${__MAX_FILE_SIZE__/1000000}MB max size)`}
            onChange={(event: any) => {
              formik.setFieldValue("file", event.currentTarget.files[0]);
            }}
            errorText={getFormErrorMessage('file')} />
        </div>
        <div className='mt-3'><Button type="submit" label="Upload" icon="pi pi-file-import"/></div>
      </form>
    </div>
  )
}
