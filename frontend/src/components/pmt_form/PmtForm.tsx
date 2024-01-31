
import { useFormik } from 'formik';
import { Button } from 'primereact/button';
import { toast } from 'react-toastify';
// import * as Yup from 'yup';
import FormText from '../form_text/FormText';
import FormDropdown from '../form_dropdown/FormDropdown';
import { INVALID_DATA_MSG } from '../../utils/constants';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import {
  ENDPOINT,
  makeMutationFunction,
} from '../../utils/DbClient';
import FormNumber from '../form_number/FormNumber';
import { range } from '../../utils/array';

interface EquipFormSchema {
  id: string;
  owner_mne: string;
  location_name: string;
  status_name: string;
  serial: string;
  comments: string;
  detector_location: string;
  type_name?: string;
  beta: number | null;
  qe: number | null;
  nominal_hv: number | null;
  model: string;
  phase1_position: string;
}

interface PmtFormProps {
  equipId?: string;
  onSuccess?: () => void;
}

export default function PmtForm({ equipId, onSuccess }: PmtFormProps) {

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

  const queryClient = useQueryClient();

  let initialValues: EquipFormSchema = {
    id: '',
    owner_mne: '',
    location_name: '',
    status_name: '',
    serial: '',
    comments: '',
    detector_location: '',
    beta: null,
    qe: null,
    nominal_hv: null,
    model: '',
    phase1_position: ''
  };

  const equipMutation = useMutation({
    mutationFn: makeMutationFunction(
      equipId ? 'put' : 'post',
      ENDPOINT['equipment']),
    onMutate: () => {
      if (equipId) {
        toast.info('Editing equipment.');
      } else {
        toast.info('Registering equipment.');
      }
    },
    onSuccess: () => {
      toast.dismiss();
      if (equipId) {
        toast.success('Equipment edited with success.');
      } else {
        toast.success('Equipment registered with success.');
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
          formik.setFieldError(key as keyof EquipFormSchema, responseData.messages[key]);
        });
      } else {
        toast.error('An error has occured.');
      }
    },
  });

  const submitData = async (data: EquipFormSchema) => {
    data['type_name'] = 'PMT';
    if (equipId) {
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

  const isPending = (
    locationQuery.isPending
    || instituteQuery.isPending
    || statusQuery.isPending
    || detectorLocationQuery.isPending);
  if (isPending) {
    return 'Loading';
  }
  const isError = (
    locationQuery.isError
    || instituteQuery.isError
    || statusQuery.isError
    || detectorLocationQuery.isError);
  if (isError) {
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

  const phase1Positions = range(49).map((i: number) => {
    return { name: String(i), value: String(i) };
  });

  const pmtModels = [
    'R11187',
    'R7877',
    'R7787',
    'R11187-SEL',
    'R11187-A1',
    'R7600-20'
  ].map((i: string) => {
    return { name: i, value: i };
  });;

  return (
    <div className="card flex justify-content-center flex-column gap-2">
      <form onSubmit={formik.handleSubmit}>
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
            errorText={getFormErrorMessage('owner_mne')} />
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
            errorText={getFormErrorMessage('location_name')} />
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
            errorText={getFormErrorMessage('status_name')} />
        </div>
        <div className='mt-3'>
          <FormDropdown
            id='detector_location'
            name='Detector Location'
            options={detectorLocations}
            value={formik.values.detector_location}
            help='Where in the detector the equipment should be.'
            onChange={(e) => {
              formik.setFieldValue('detector_location', e.value);
            }}
            errorText={getFormErrorMessage('detector_location')} />
        </div>
        <div className='mt-3'>
          <FormText
            id='serial'
            name='Serial'
            required
            value={formik.values.serial}
            help='Equipment serial number (Identification code issued by the manufacturer)'
            onChange={(e) => {
              formik.setFieldValue('serial', e.target.value);
            }}
            errorText={getFormErrorMessage('serial')} />
        </div>
        <div className='mt-3'>
          <FormNumber
            id='beta'
            name='Beta'
            value={formik.values.comments}
            help='PMT Beta value. Must be a positive number.'
            onChange={(e) => {
              formik.setFieldValue('beta', e.value);
            }}
            errorText={getFormErrorMessage('beta')} />
        </div>
        <div className='mt-3'>
          <FormNumber
            id='nominal_hv'
            name='Nominal HV'
            value={formik.values.comments}
            help='PMT Nominal HV value. Must be a positive number.'
            onChange={(e) => {
              formik.setFieldValue('nominal_hv', e.value);
            }}
            errorText={getFormErrorMessage('nominal_hv')} />
        </div>
        <div className='mt-3'>
          <FormNumber
            id='qe'
            name='QE'
            value={formik.values.comments}
            help='PMT QE value. Must be a positive number.'
            onChange={(e) => {
              formik.setFieldValue('qe', e.value);
            }}
            errorText={getFormErrorMessage('qe')} />
        </div>
        <div className='mt-3'>
          <FormDropdown
            id='model'
            name='Model'
            options={pmtModels}
            value={formik.values.model}
            help='Where in the detector the equipment should be.'
            onChange={(e) => {
              formik.setFieldValue('model', e.value);
            }}
            errorText={getFormErrorMessage('model')} />
        </div>
        <div className='mt-3'>
          <FormDropdown
            id='phase1_position'
            name='Phase 1 Position'
            options={phase1Positions}
            value={formik.values.phase1_position}
            help='PMT Position in the old Phase 1 Drawer'
            onChange={(e) => {
              formik.setFieldValue('phase1_position', e.value);
            }}
            errorText={getFormErrorMessage('phase1_position')} />
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
            errorText={getFormErrorMessage('comments')} />
        </div>
        <div className='mt-3'>
          <Button type="submit" label="Submit" />
        </div>
      </form>
    </div>
  )
}
