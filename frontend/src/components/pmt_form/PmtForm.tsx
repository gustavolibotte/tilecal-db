import { useFormik } from 'formik';
import { Button } from 'primereact/button';
import { Id, toast } from 'react-toastify';
import FormText from '../form_text/FormText';
import FormDropdown from '../form_dropdown/FormDropdown';
import { InputTextarea } from 'primereact/inputtextarea';
import { useQuery, useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import {
  defaultQueryFunction,
  getEndpoint,
  makeMutationFunction,
} from '../../utils/DbClient';
import FormNumber from '../form_number/FormNumber';
import { range } from '../../utils/array';
import { useRef, useEffect } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';
import {
  commentsRules,
  serialRules,
} from '../../utils/validation_rules';
import * as Yup from 'yup';

interface PMTFormSchema {
  id: string;
  owner_mne: string;
  location_name: string;
  status_name: string;
  serial: string;
  comments: string;
  detector_location_name: string;
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
  isEdit?: boolean;
}

export default function PmtForm({ equipId, onSuccess, isEdit }: PmtFormProps) {

  const locationQuery: any = useQuery({
    queryKey: ['location', 'get', {}],
    queryFn: defaultQueryFunction
  });

  const instituteQuery: any = useQuery({
    queryKey: ['institute', 'get', {}],
    queryFn: defaultQueryFunction
  });

  const statusQuery: any = useQuery({
    queryKey: ['status', 'get', {}],
    queryFn: defaultQueryFunction
  });

  const detectorLocationQuery: any = useQuery({
    queryKey: ['detector_location', 'get', {}],
    queryFn: defaultQueryFunction
  });

  const {
    data: equipData,
    isPending: isEquipPending
  } = useQuery({
    queryKey: ['equipment', 'get', { id: equipId }],
    queryFn: defaultQueryFunction,
    enabled: !!equipId,
  });

  let initialValues: PMTFormSchema = {
    id: '',
    owner_mne: '',
    location_name: '',
    status_name: '',
    serial: '',
    comments: 'Was tested at ',
    detector_location_name: '',
    beta: null,
    qe: null,
    nominal_hv: null,
    model: '',
    phase1_position: ''
  };

  const toastId = useRef<Id>();

  const equipMutation = useMutation({
    mutationFn: makeMutationFunction(
      equipId ? 'put' : 'post',
      getEndpoint('equipment')),
    onMutate: () => {
      if (equipId) {
        toastId.current = toast.info('Editing equipment.', midTransactionToastConfig);
      } else {
        toastId.current = toast.info('Registering equipment.', midTransactionToastConfig);
      }
    },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      if (equipId) {
        toast.success('Equipment edited with success.');
      } else {
        toast.success('Equipment registered with success.');
      }

      if (onSuccess) {
        onSuccess();
      }

      if (!equipId) {
        formik.resetForm();
      }
    },
    onError: (error: AxiosError) => {
      toast.dismiss(toastId.current);
      const responseData: any = error?.response?.data;
      if (responseData.message == __INVALID_DATA_MSG__) {
        Object.keys(responseData.data).forEach((key: string) => {
          formik.setFieldError(key as keyof PMTFormSchema, responseData.data[key]);
        });
      } else {
        console.log(error);
        toast.error('An error has occurred.');
      }
    },
  });

  const submitData = async (data: PMTFormSchema) => {
    data['type_name'] = 'PMT';
    if (equipId) {
      data['id'] = equipId;
    }
    equipMutation.mutate(data);
  };

  const validationSchema: any = {
    serial: serialRules,
    comments: commentsRules,
    beta: Yup.number().positive(__FIELD_POSITIVE_MSG__).nullable(),
    nominal_hv: Yup.number().positive(__FIELD_POSITIVE_MSG__).nullable(),
    qe: Yup.number().positive(__FIELD_POSITIVE_MSG__).nullable()
  }
  if (!isEdit) {
    validationSchema['owner_mne'] = Yup.string().required(__FIELD_REQUIRED_MSG__);
    validationSchema['location_name'] = Yup.string().required(__FIELD_REQUIRED_MSG__);
    validationSchema['status_name'] = Yup.string().required(__FIELD_REQUIRED_MSG__);
  }

  const formik = useFormik({
    initialValues: initialValues,
    onSubmit: submitData,
    validationSchema: Yup.object(validationSchema)
  });

  useEffect(() => {
    const responseData: any = equipData?.data || [];
    const equipArray = (!Array.isArray(responseData) && responseData.data !== undefined) 
      ? responseData.data 
      : responseData;

    const equipment = equipArray[0];

    if (equipment) {
      formik.setValues({
        ...initialValues,
        ...equipment,
        comments: equipment.comments || '',
        owner_mne: equipment.owner_mne || '',
        location_name: equipment.location_name || '',
        status_name: equipment.status_name || '',
        serial: equipment.serial || '',
        detector_location_name: equipment.detector_location_name || '',
        beta: equipment.type_params?.beta || null,
        qe: equipment.type_params?.qe || null,
        nominal_hv: equipment.type_params?.nominal_hv || null,
        model: equipment.type_params?.model || '',
        phase1_position: equipment.type_params?.phase1_position || ''
      });
    }
  }, [equipData]);

  const getFormErrorMessage = (name: keyof PMTFormSchema): string | null => {
    return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };
  
  const isPending = (
    locationQuery.isPending
    || instituteQuery.isPending
    || statusQuery.isPending
    || detectorLocationQuery.isPending
    || (isEdit && isEquipPending)
  );

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
      name: `${institute.mne} - ${institute.name}`,
      value: institute.mne
    };
  });

  const statusNameOptions = statusQuery.data.data.map((status: any) => {
    return {
      name: `${status.name} - ${status.description}`,
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
    'R7600-20',
    'R5900MOD'
  ].map((i: string) => {
    return { name: i, value: i };
  });

  return (
    <div className="card flex justify-content-center flex-column gap-2">
      <form onSubmit={formik.handleSubmit}>
        <div className='mt-3'>
          <FormDropdown
            id='owner_mne'
            name='Owner'
            options={ownerMneOptions}
            required={!isEdit}
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
            required={!isEdit}
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
            required={!isEdit}
            value={formik.values.status_name}
            help='Current equipment status'
            onChange={(e) => {
              formik.setFieldValue('status_name', e.value);
            }}
            errorText={getFormErrorMessage('status_name')} />
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
            errorText={getFormErrorMessage('detector_location_name')} />
        </div>
        <div className='mt-3'>
          <FormText
            id='serial'
            name='Serial'
            required={!isEdit}
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
            value={formik.values.beta}
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
            value={formik.values.nominal_hv}
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
            value={formik.values.qe}
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

        <div className='mt-3 flex flex-column gap-2'>
          <label htmlFor='comments' className='font-bold text-600'>Comments</label>
          <InputTextarea
            id='comments'
            value={formik.values.comments}
            onChange={(e) => {
              formik.setFieldValue('comments', e.target.value);
            }}
            rows={4}
            autoResize
            onKeyDown={(e) => {
              if (e.key === 'Enter') {
                e.stopPropagation();
              }
            }}
            className={getFormErrorMessage('comments') ? 'p-invalid' : ''}
          />
          <small className="text-500">Remarks about the equipment</small>
          {getFormErrorMessage('comments') && <small className="p-error">{getFormErrorMessage('comments')}</small>}
        </div>

        <div className='mt-4'>
          <Button type="submit" label="Submit" />
        </div>
      </form>
    </div>
  )
}