import { useFormik } from 'formik';
import { toast } from 'react-toastify';
import { DEFAULT_ERROR_MSG, INVALID_DATA_MSG, MAX_FILE_SIZE } from '../../utils/constants';
import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import FormText from '../form_text/FormText';
import { ENDPOINT, makeMutationFunction } from '../../utils/DbClient';
import FormTextArea from '../form_text_area/FormTextArea';
import { Button } from 'primereact/button';

import './file_form.css';
import FormFile from '../form_file/FormFile';

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

  const fileMutation = useMutation({
    mutationFn: makeMutationFunction(
      'post',
      fileId? ENDPOINT['file_put']: ENDPOINT['file'],
      headers),
    onMutate: () => {
      toast.info('Uploading file.', {
        autoClose: false,
        hideProgressBar:true
      });
    },
    onSuccess: () => {
      toast.dismiss();
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
      formData.append('file', data.file);
    }
    if (fileId){
      console.log(fileId);
      formData.append('id', fileId);
    }
    formData.append('name', data.name);
    formData.append('description', data.description);
    console.log(data);
    console.log(formData);
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
            help={`File to upload (${MAX_FILE_SIZE/1000000}MB max size)`}
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
