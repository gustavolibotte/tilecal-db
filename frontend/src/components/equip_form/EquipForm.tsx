
import { useFormik } from 'formik';
import { Button } from 'primereact/button';
import { toast } from 'react-toastify';
import * as Yup from 'yup';
import FormText from '../form_text/FormText';
import FormDropdown from '../form_dropdown/FormDropdown';
import { EQUIPMENT_ENDPOINT } from '../../utils/routes';
import { INVALID_DATA_MSG } from '../../utils/constants';
import { useQuery } from '@tanstack/react-query';
import { locationGet } from '../../db_client/Location';
import axios from 'axios';

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
}

export default function EquipForm({typeName}: EquipFormProps) {

  const locationQuery = useQuery({
    queryKey: ['location', {}],
    queryFn: locationGet
  });

  const ownerMneOptions = [
    {name: 'CERN', value:'CER'},
    {name: 'UFRJ', value:'2'}
  ];

  const statusNameOptions = [
    {name: 'Installed', value: 'Installed'},
    {name: 'Bad', value: '2'}
  ]

  const detectorLocations = [
    { name: 'EBA01', value: 'EBA01'},
    { name: 'EBA02', value: 'EBA02'},
  ]

  let initialValues: EquipFormSchema = {
    id: '',
    owner_mne: '',
    location_name: '',
    status_name: '',
    serial: '',
    comments: '',
    detector_location: ''
  };

  const submitData = async (data: EquipFormSchema) =>{
    data['type_name'] = typeName;
    console.log(data);
    await axios.post(
      EQUIPMENT_ENDPOINT,
      data
    ).then((response) => {
      toast.success('Data submitted with success');
      formik.resetForm();
    }, (error) => {
      const responseData = error.response.data;
      // console.log(error);
      // console.log(responseData);
      if (responseData.error == INVALID_DATA_MSG){
        Object.keys(responseData.messages).forEach((key: string) => {
          console.log(`Setting error`);
          console.log(key);
          console.log(responseData.messages[key]);
          formik.setFieldError(key as keyof EquipFormSchema, responseData.messages[key]);
        });
      }
      toast.error(responseData.error);
    }
    )
  }

  const formik = useFormik({
      initialValues: initialValues,
      validationSchema: Yup.object({
        // id: Yup.string().required('Equipment ID is required.'),
        owner_mne: Yup.string().required('An owner institute is required.'),
        location_name: Yup.string().required('An equipment location is required.'),
        status_name: Yup.string().required('Equipment status is required.')
      }),
      onSubmit: submitData
  });
  
  const getFormErrorMessage = (name: keyof EquipFormSchema): string | null => {
      return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };

  if (locationQuery.isPending){
    return 'Loading';
  }
  const locationNameOptions = locationQuery.data.map((location: any) => {
    return {
      name: location.name,
      value: location.name
    };
  })

  return (
      <div className="card flex justify-content-center">
          <form onSubmit={formik.handleSubmit} className="flex flex-column gap-2">
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
