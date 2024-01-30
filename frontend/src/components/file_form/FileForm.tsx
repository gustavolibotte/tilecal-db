import { useFormik } from 'formik';
import { toast } from 'react-toastify';
import { DEFAULT_ERROR_MSG, INVALID_DATA_MSG, MAX_FILE_SIZE } from '../../utils/constants';
import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { FileUpload, FileUploadSelectEvent } from 'primereact/fileupload';
import FormText from '../form_text/FormText';
import { ENDPOINT, makeMutationFunction } from '../../utils/DbClient';
import FormTextArea from '../form_text_area/FormTextArea';
import { Button } from 'primereact/button';
import { useRef, useState } from 'react';
import * as Tup from 'yup';

import './file_form.css';

interface FileFormSchema {
  name: string;
  file?: File;
  description: string;
}

interface FileFormProps {
  fileId?: string
}

export default function FileForm({ fileId }: FileFormProps) {

  const initialValues = {
    name: '', description: '', file: undefined
  };


  const headers = {
    'Content-Type': 'multipart/form-data'
  };
  const fileMutation = useMutation({
    mutationFn: makeMutationFunction(
      fileId ? 'put' : 'post',
      ENDPOINT['file'],
      headers),
    onMutate: () => {
      toast.info('Uploading file.', {
        autoClose: false,
        hideProgressBar:true
      });
    },
    onSuccess: () => {
      toast.dismiss();
      toast.success('File uploaded with success');
      formik.resetForm();
    },
    onError: (error: AxiosError) => {
      // The following "?" fields are guaranteed with the app
      toast.dismiss();
      const responseData: any = error?.response?.data;
      if (responseData.error == INVALID_DATA_MSG) {
        Object.keys(responseData.messages).forEach((key: string) => {
          formik.setFieldError(key as keyof FileFormSchema, responseData.messages[key]);
        });
      } else {
        toast.error(DEFAULT_ERROR_MSG);
        console.log(error);
      }
    },
  });

  const submitData = async (data: FileFormSchema) => {
    const formData = new FormData();
    if (data.file){
      formData.append('file', data.file)
    }
    formData.append('name', data.name);
    formData.append('description', data.description);
    fileMutation.mutate(data);
    console.log(data);
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
            required
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
          <input className='p-button' id="file" name="file" type="file" onChange={(event: any) => {
            formik.setFieldValue("file", event.currentTarget.files[0]);
          }} />
        </div>
        <div className='mt-3'><Button type="submit" label="Upload" icon="pi pi-file-import"/></div>
      </form>
    </div>
  )
}
