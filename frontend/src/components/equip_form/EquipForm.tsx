
import { useFormik } from 'formik';
import { Button } from 'primereact/button';
import { toast } from 'react-toastify';
import FormText from '../form_text/FormText';
import FormDropdown from '../form_dropdown/FormDropdown';
import { INVALID_DATA_MSG } from '../../utils/constants';
import { useQuery, useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import {
  ENDPOINT,
  makeMutationFunction
} from '../../utils/DbClient';

interface EquipFormSchema {
  id: string;
  owner_mne: string;
  location_name: string;
  status_name: string;
  serial: string;
  comments: string;
  detector_location: string;
  type_name?: string;
}

interface EquipFormProps {
  typeName: string;
  equipId?: string
}

export default function EquipForm({typeName, equipId}: EquipFormProps) {

  let initialValues: EquipFormSchema = {
    id: '',
    owner_mne: '',
    location_name: '',
    status_name: '',
    serial: '',
    comments: '',
    detector_location: ''
  };

  const equipMutation = useMutation({
    mutationFn: makeMutationFunction(
      equipId? 'put': 'post', 
      ENDPOINT['equipment']),
    onMutate: () => {
      toast.info('Registering equipment.');
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
      if (responseData.error == INVALID_DATA_MSG){
        Object.keys(responseData.messages).forEach((key: string) => {
          formik.setFieldError(key as keyof EquipFormSchema, responseData.messages[key]);
        });
      } else{
        toast.error('An error has occured.');
      }
    },
  });

  const submitData = async (data: EquipFormSchema) =>{
    data['type_name'] = typeName;
    if (equipId){
      data['id'] = equipId;
    }
    equipMutation.mutate(data);
  }

  const formik = useFormik({
      initialValues: initialValues,
      onSubmit: submitData
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
      <div className="card flex justify-content-center">
          <form onSubmit={formik.handleSubmit} className="flex flex-column gap-2">
            {!equipId && <FormText
              id='id'
              name='ID'
              required
              value={formik.values.id}
              help='Equipment ID (Use the barcode)'
              onChange={(e) => {
                formik.setFieldValue('id', e.target.value);
              }}
              errorText={getFormErrorMessage('id')}/>}
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
            <FormDropdown
              id='detector_location'
              name='Detector Location'
              options={detectorLocations}
              value={formik.values.detector_location}
              help='Where in the detector the equipment should be.'
              onChange={(e) => {
                formik.setFieldValue('detector_location', e.value);
              }}
              errorText={getFormErrorMessage('detector_location')}/>
            <FormText
              id='serial'
              name='Serial'
              value={formik.values.serial}
              help='Equipment serial number (Identification code issued by the manufacturer)'
              onChange={(e) => {
                formik.setFieldValue('serial', e.target.value);
              }}
              errorText={getFormErrorMessage('serial')}/>
            <FormText
              id='comments'
              name='Comments'
              value={formik.values.comments}
              help='Remarks about the equipment'
              onChange={(e) => {
                formik.setFieldValue('comments', e.target.value);
              }}
              errorText={getFormErrorMessage('comments')}/>
            <Button type="submit" label="Submit" />
          </form>
      </div>
  )
}
