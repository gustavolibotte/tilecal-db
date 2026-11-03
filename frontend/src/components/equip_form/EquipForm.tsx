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
import { useRef, useEffect, useState, useMemo } from 'react';
import { midTransactionToastConfig } from '../../utils/toast';
import {
  commentsRules,
  serialRules,
} from '../../utils/validation_rules';
import * as Yup from 'yup';
import { Message } from 'primereact/message';

import { applyAtlasBarcodeMask } from '../../utils/string'; 

// This interface defines the fields for a generic equipment form.
interface EquipFormSchema {
  id: string;
  owner_mne: string;
  location_name: string;
  status_name: string;
  detector_location_name: string;
  serial: string;
  comments: string;
  type_name?: string;
  box_position: number | null;
}

interface EquipFormProps {
  equipId?: string;
  typeName: string;
  onSuccess?: () => void;
  isEdit?: boolean;
}

export default function EquipForm({ equipId, typeName, onSuccess, isEdit }: EquipFormProps) {

  // State to track the original location before edits
  const [originalLocation, setOriginalLocation] = useState<string>('');

  const isPmtBlock = ['PMT Block', 'PMT Block (Standard)', 'PMT Block (Long Cable)'].includes(typeName);
  const originalValues = useRef<Record<string, any>>({});
  const detectorLocationQuery: any = useQuery({
    queryKey: ['detector_location', 'get', {}], queryFn: defaultQueryFunction
  });
  const rows = (response: any): any[] => {
    const data = response?.data;
    return Array.isArray(data) ? data : Array.isArray(data?.data) ? data.data : [];
  };

  // Queries to populate dropdown options and get regex rules
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

  const equipTypesQuery: any = useQuery({ 
    queryKey: ['equipment_type', 'get', {}],
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

  // Fetch related equipments to determine if this component has assembled children
  const relatedEquipQuery: any = useQuery({
    queryKey: ['equipment', 'get', { relatedEquipmentId: equipId }],
    queryFn: defaultQueryFunction,
    enabled: !!equipId && isEdit,
  });

  const hasChildren = useMemo(() => {
    if (!relatedEquipQuery.data?.data) return false;
    const related = Array.isArray(relatedEquipQuery.data.data) ? relatedEquipQuery.data.data : relatedEquipQuery.data.data.data;
    return related?.some((eq: any) => eq.relation_type === 'child');
  }, [relatedEquipQuery.data]);

  let initialValues: EquipFormSchema = {
    id: '',
    owner_mne: '',
    location_name: '',
    status_name: '',
    detector_location_name: '',
    serial: '',
    comments: 'Was tested at ',
    box_position: null,
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
      if (responseData?.message == __INVALID_DATA_MSG__) {
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
    const editable = ['owner_mne', 'location_name', 'detector_location_name', 'status_name', 'serial', 'comments'] as const;
    const payload: Record<string, any> = { id: equipId || data.id };
    if (!equipId) {
      payload.type_name = typeName;
      if (isPmtBlock) payload.box_position = data.box_position;
    }
    for (const field of editable) {
      const value = data[field] ?? '';
      if (!equipId || value !== (originalValues.current[field] ?? '')) payload[field] = value;
    }
    if (equipId && Object.keys(payload).length === 1) { onSuccess?.(); return; }
    equipMutation.mutate(payload);
  };

  const validationSchema: any = {
    owner_mne: Yup.string().required(__FIELD_REQUIRED_MSG__),
    location_name: Yup.string().required(__FIELD_REQUIRED_MSG__),
    status_name: Yup.string().required(__FIELD_REQUIRED_MSG__),
    box_position: Yup.number().integer().min(0).max(48).nullable(),
    serial: serialRules,
    comments: commentsRules,
  }
  if (!isEdit) {
    validationSchema['id'] = Yup.string().required(__FIELD_REQUIRED_MSG__);
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
    const responseData: any = equipData?.data || [];
    const equipArray = (!Array.isArray(responseData) && responseData.data !== undefined) 
      ? responseData.data 
      : responseData;

    // The API returns an array, so we take the first element
    const equipment = equipArray[0];

    if (equipment) {
      originalValues.current = equipment;
      setOriginalLocation(equipment.location_name || '');
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
        detector_location_name: equipment.detector_location_name || '',
      });
    }
  }, [equipData]); // This effect runs whenever equipData changes

  const getFormErrorMessage = (name: keyof EquipFormSchema): string | null => {
    return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };

  // Add the new query's pending state to the overall loading check
  const isPending = (
    detectorLocationQuery.isPending
    || locationQuery.isPending
    || instituteQuery.isPending
    || statusQuery.isPending
    || equipTypesQuery.isPending
    || (isEdit && isEquipPending)
    || (isEdit && relatedEquipQuery.isPending)
  );
  if (isPending) {
    return 'Loading';
  }
  const isError = (
    detectorLocationQuery.isError
    || (isEdit && !!equipId && !equipData)
    || locationQuery.isError
    || instituteQuery.isError
    || statusQuery.isError
    || equipTypesQuery.isError
    || (isEdit && relatedEquipQuery.isError)
  );
  if (isError) {
    return 'Error';
  }

  const locationNameOptions = rows(locationQuery.data).map((location: any) => {
    return {
      name: location.description ? `${location.name} - ${location.description}` : location.name,
      value: location.name
    };
  })

  const ownerMneOptions = rows(instituteQuery.data).map((institute: any) => {
    return {
      name: `${institute.mne} - ${institute.name}`,
      value: institute.mne
    };
  });

  const statusNameOptions = rows(statusQuery.data).map((status: any) => {
    return {
      name: `${status.name} - ${status.description}`,
      value: status.name
    };
  });

  const detectorOptions = rows(detectorLocationQuery.data).map(item => ({ name: item.name, value: item.name }));
  // Keep an existing value visible even if its catalogue entry was retired.
  for (const [field, options] of [
    ['owner_mne', ownerMneOptions], ['location_name', locationNameOptions],
    ['status_name', statusNameOptions], ['detector_location_name', detectorOptions],
  ] as const) {
    const value = formik.values[field];
    if (value && !options.some(option => option.value === value)) options.push({ name: `${value} (current value)`, value });
  }
  return (
    <div className="card flex justify-content-center flex-column gap-2">
      <form onSubmit={formik.handleSubmit}>
        
        {!isEdit && (
          <div className='mt-3'>
            <FormText
              id='id'
              name='ID (Scan Barcode)'
              required={true}
              value={formik.values.id}
              help='Scan ATLAS barcode or type manually'
              onChange={(e) => {
                const selectedType = equipTypesQuery.data?.data?.find((t: any) => t.name === typeName);
                const regexPattern = selectedType ? selectedType.id_regex : null;
                
                const maskedId = applyAtlasBarcodeMask(e.target.value, regexPattern);
                formik.setFieldValue('id', maskedId);
              }}
              errorText={getFormErrorMessage('id')} 
            />
          </div>
        )}

        <div className='mt-3'>
          <FormDropdown
            id='owner_mne'
            name='Owner'
            options={ownerMneOptions}
            required={true}
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
            required={true}
            value={formik.values.location_name}
            help='Current equipment location'
            onChange={(e) => {
              formik.setFieldValue('location_name', e.value);
            }}
            errorText={getFormErrorMessage('location_name')} />

            {isEdit && hasChildren && formik.values.location_name !== originalLocation && (
              <Message 
                severity="warn" 
                text="Update alert: the location of all assembled components will also be automatically updated." 
                className="mt-2 w-full justify-content-start" 
              />
            )}
        </div>
        <div className='mt-3'>
          <FormDropdown id='detector_location_name' name='Detector Location'
            options={detectorOptions} value={formik.values.detector_location_name}
            help='Current detector position. Clear to remove the assignment.'
            onChange={e => formik.setFieldValue('detector_location_name', e.value ?? '')}
            errorText={getFormErrorMessage('detector_location_name')} />
        </div>
        <div className='mt-3'>
          <FormDropdown
            id='status_name'
            name='Status'
            options={statusNameOptions}
            required={true}
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

        {!isEdit && isPmtBlock && <div className="mt-3">
          <FormDropdown id="box_position" name="Upgrade Position" required={false}
            options={Array.from({ length: 49 }, (_, i) => ({ name: String(i), value: i }))}
            value={formik.values.box_position}
            help="Optional position reserved for this block."
            onChange={e => formik.setFieldValue('box_position', e.value ?? null)}
            errorText={getFormErrorMessage('box_position')} />
        </div>}
        <div className='mt-4'>
          <Button type="submit" label="Save equipment" loading={equipMutation.isPending} disabled={equipMutation.isPending} />
        </div>
      </form>
    </div>
  )
}