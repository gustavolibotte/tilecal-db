
import { useFormik } from 'formik';
import { Button } from 'primereact/button';
import { Id, toast } from 'react-toastify';
import FormText from '../form_text/FormText';
import FormDropdown from '../form_dropdown/FormDropdown';
import { useQuery, useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import {
  getEndpoint,
  makeMutationFunction
} from '../../utils/DbClient';
import { useRef } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';
import { preventEnterDefault } from '../../utils/event';
import * as Yup from 'yup';
import { 
  commentsRules,
  equipIdRules,
  extractSequentialNumber,
  serialRules,
} from '../../utils/validation_rules';

interface EquipFormSchema {
  id: string;
  owner_mne: string;
  location_name: string;
  status_name: string;
  serial: string;
  comments: string;
  detector_location_name: string;
  type_name?: string;
}

interface EquipFormProps {
  typeName: string;
  equipId?: string
  onSuccess?: () => void 
}

export default function EquipForm({typeName, equipId, onSuccess}: EquipFormProps) {

  let initialValues: EquipFormSchema = {
    id: '',
    owner_mne: '',
    location_name: '',
    status_name: '',
    serial: '',
    comments: '',
    detector_location_name: ''
  };
  const toastId = useRef<Id>();

  const equipMutation = useMutation({
    mutationFn: makeMutationFunction(
      equipId? 'put': 'post', 
      getEndpoint('equipment')),
    onMutate: () => {
      if (equipId){
        toastId.current = toast.info('Editing equipment.', midTransactionToastConfig);
      } else{
        toastId.current = toast.info('Registering equipment.', midTransactionToastConfig);
      }
    },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      if (equipId){
        toast.success('Equipment edited with success');
      } else{
        toast.success('Equipment registered with success');
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
      if (responseData.message == __INVALID_DATA_MSG__){
        Object.keys(responseData.data).forEach((key: string) => {
          formik.setFieldError(key as keyof EquipFormSchema, responseData.data[key]);
        });
      } else{
        console.log(error);
        toast.error('An error has occured.');
      }
    },
  });

  const submitData = async (data: EquipFormSchema) =>{
    data['type_name'] = typeName;
    if (equipId){
      data['id'] = extractSequentialNumber(equipId);
    }
    equipMutation.mutate(data);
  }

  const formik = useFormik({
      initialValues: initialValues,
      onSubmit: submitData,
      validationSchema: Yup.object({
        id: equipIdRules.required(__FIELD_REQUIRED_MSG__),
        owner_mne: Yup.string().required(__FIELD_REQUIRED_MSG__),
        location_name: Yup.string().required(__FIELD_REQUIRED_MSG__),
        status_name: Yup.string().required(__FIELD_REQUIRED_MSG__),
        serial: serialRules,
        comments: commentsRules
      })
  });
  
  const getFormErrorMessage = (name: keyof EquipFormSchema): string | null => {
      return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };

  const locationQuery: any = useQuery({
    queryKey: ['location', 'get', {}]
  });

  const instituteQuery: any = useQuery({
    queryKey: ['institute', 'get', {}]
  });

  const statusQuery: any = useQuery({
    queryKey: ['status', 'get', {}]
  });

  const detectorLocationQuery: any = useQuery({
    queryKey: ['detector_location', 'get', {}]
  });

  const isPending = (
    locationQuery.isPending
    || instituteQuery.isPending
    || statusQuery.isPending
    || detectorLocationQuery.isPending);
  if (isPending){
    return 'Loading';
  }
  const isError = (
    locationQuery.isError
    || instituteQuery.isError
    || statusQuery.isError
    || detectorLocationQuery.isError);
  if (isError){
    return 'Error';
  }

  const locationNameOptions = locationQuery.data.data.map((location: any) => {
    return {
      name: location.name,
      value: location.name
    };
  })

  const ownerMneOptions = instituteQuery.data.data.map((institute: any) => {
    return {
      name: institute.name,
      value: institute.mne
    };
  });

  const statusNameOptions = statusQuery.data.data.map((status: any) => {
    return {
      name: status.name,
      value: status.name
    };
  });

  const detectorLocations = detectorLocationQuery.data.data.map((detector_location: any) => {
    return {
      name: detector_location.name,
      value: detector_location.name
    };
  });

  return (
    <div className="card flex justify-content-center flex-column gap-2">
        <form onSubmit={formik.handleSubmit} onKeyDown={preventEnterDefault}>
          {!equipId && 
          <div className='mt-3'>
            <FormText
              id='id'
              name='ID'
              required
              value={formik.values.id}
              help='Equipment ID (Use the barcode)'
              onChange={(e) => {
                formik.setFieldValue('id', e.target.value);
              }}
              errorText={getFormErrorMessage('id')}/>
          </div>}
          <div className='mt-3'>
            <FormDropdown
              id='owner_mne'
              name='Owner'
              options={ownerMneOptions}
              required
              value={formik.values.owner_mne}
              help='Institute that owns the equipment'
              onChange={(e) => {
                formik.setFieldValue('owner_mne', e.value);
              }}
              errorText={getFormErrorMessage('owner_mne')}/>
          </div>
          <div className='mt-3'>
            <FormDropdown
              id='location_name'
              name='Location'
              options={locationNameOptions}
              required
              value={formik.values.location_name}
              help='Current equipment location'
              onChange={(e) => {
                formik.setFieldValue('location_name', e.value);
              }}
              errorText={getFormErrorMessage('location_name')}/>
          </div>
          <div className='mt-3'>
            <FormDropdown
              id='status_name'
              name='Status'
              options={statusNameOptions}
              required
              value={formik.values.status_name}
              help='Current equipment status'
              onChange={(e) => {
                formik.setFieldValue('status_name', e.value);
              }}
              errorText={getFormErrorMessage('status_name')}/>
          </div>
          <div className='mt-3'>
            <FormDropdown
              id='detector_location_name'
              name='Detector Location'
              options={detectorLocations}
              value={formik.values.detector_location_name}
              help='Where in the detector the equipment should be.'
              onChange={(e) => {
                formik.setFieldValue('detector_location_name', e.value);
              }}
              errorText={getFormErrorMessage('detector_location_name')}/>
          </div>
          <div className='mt-3'>
            <FormText
              id='serial'
              name='Serial'
              value={formik.values.serial}
              help='Equipment serial number (Identification code issued by the manufacturer)'
              onChange={(e) => {
                formik.setFieldValue('serial', e.target.value);
              }}
              errorText={getFormErrorMessage('serial')}/>
          </div>
          <div className='mt-3'>
          <FormText
            id='comments'
            name='Comments'
            value={formik.values.comments}
            help='Remarks about the equipment'
            onChange={(e) => {
              formik.setFieldValue('comments', e.target.value);
            }}
            errorText={getFormErrorMessage('comments')}/>
          </div>
          <div className='mt-3'><Button type="submit" label="Submit" /></div>
        </form>
    </div>
  )
}
