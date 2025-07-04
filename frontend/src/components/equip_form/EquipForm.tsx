import { useFormik } from 'formik';
import { Button } from 'primereact/button';
import { Id, toast } from 'react-toastify';
import FormText from '../form_text/FormText';
import FormDropdown from '../form_dropdown/FormDropdown';
import { useQuery, useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import {
  defaultQueryFunction,
  getEndpoint,
  makeMutationFunction,
} from '../../utils/DbClient';
import { useRef, useEffect } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';
import {
  commentsRules,
  serialRules,
} from '../../utils/validation_rules';
import * as Yup from 'yup';

// This interface defines the fields for a generic equipment form.
interface EquipFormSchema {
  id: string;
  owner_mne: string;
  location_name: string;
  status_name: string;
  serial: string;
  comments: string;
  type_name?: string;
}

interface EquipFormProps {
  equipId?: string;
  typeName: string;
  onSuccess?: () => void;
  isEdit?: boolean;
}

export default function EquipForm({ equipId, typeName, onSuccess, isEdit }: EquipFormProps) {

  // Queries to populate dropdown options
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

  // Fetch equipment data when in edit mode
  const {
    data: equipData,
    isPending: isEquipPending
  } = useQuery({
    queryKey: ['equipment', 'get', { id: equipId }],
    queryFn: defaultQueryFunction,
    // This query will only run if equipId is provided
    enabled: !!equipId,
  });

  let initialValues: EquipFormSchema = {
    id: '',
    owner_mne: '',
    location_name: '',
    status_name: '',
    serial: '',
    comments: 'Was tested at ',
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

      // This calls the parent function which closes the dialog
      if (onSuccess) {
        onSuccess();
      }

      // Only reset the form if it was NOT an edit operation
      if (!equipId) {
        formik.resetForm();
      }
    },
    onError: (error: AxiosError) => {
      toast.dismiss(toastId.current);
      const responseData: any = error?.response?.data;
      if (responseData.message == __INVALID_DATA_MSG__) {
        Object.keys(responseData.data).forEach((key: string) => {
          formik.setFieldError(key as keyof EquipFormSchema, responseData.data[key]);
        });
      } else {
        console.log(error);
        toast.error('An error has occurred.');
      }
    },
  });

  const submitData = async (data: EquipFormSchema) => {
    // Use the typeName prop to set the equipment type dynamically
    data['type_name'] = typeName;
    if (equipId) {
      data['id'] = equipId;
    }
    equipMutation.mutate(data);
  };

  const validationSchema: any = {
    serial: serialRules,
    comments: commentsRules,
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

  // Use an effect to populate the form once the equipment data is fetched
  useEffect(() => {
    // The API returns an array, so we take the first element
    const equipment = equipData?.data?.[0];

    if (equipment) {
      // Use setValues to populate the form with the fetched data
      formik.setValues({
        ...initialValues, // Ensures all fields from the schema are present
        ...equipment, // Overwrites with values from the API response
        // Ensure null values from the API don't break the components
        comments: equipment.comments || '',
        owner_mne: equipment.owner_mne || '',
        location_name: equipment.location_name || '',
        status_name: equipment.status_name || '',
        serial: equipment.serial || '',
      });
    }
  }, [equipData]); // This effect runs whenever equipData changes

  const getFormErrorMessage = (name: keyof EquipFormSchema): string | null => {
    return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };

  // Add the new query's pending state to the overall loading check
  const isPending = (
    locationQuery.isPending
    || instituteQuery.isPending
    || statusQuery.isPending
    || (isEdit && isEquipPending)
  );
  if (isPending) {
    return 'Loading';
  }
  const isError = (
    locationQuery.isError
    || instituteQuery.isError
    || statusQuery.isError
  );
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