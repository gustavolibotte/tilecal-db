
import { useFormik } from 'formik';
import { Button } from 'primereact/button';
import { toast } from 'react-toastify';
import * as Yup from 'yup';
import FormText from '../form_text/FormText';
import FormDropdown from '../form_dropdown/FormDropdown';
import axios from 'axios';
import { EQUIPMENT_ENDPOINT } from '../../utils/routes';

interface EquipFormSchema {
  id: string;
  owner: string;
  location: string;
  status: string;
  serial: string;
  comments: string;
  detector_location: string;
}

export default function EquipForm() {

  const ownerOptions = [
    {name: 'CERN', value:'CER'},
    {name: 'UFRJ', value:'2'}
  ];
  const locationOptions = [
    {name: 'Building 175', value: 'B175'},
    {name: 'CERN', value: 'CER'}
  ]

  const statusOptions = [
    {name: 'Installed', value: 'Installed'},
    {name: 'Bad', value: '2'}
  ]

  const detectorLocations = [
    { name: 'EBA01', value: 'EBA01'},
    { name: 'EBA02', value: 'EBA02'},
  ]

  let initialValues: EquipFormSchema = {
    id: '',
    owner: '',
    location: '',
    status: '',
    serial: '',
    comments: '',
    detector_location: ''
  };

  const show = () => {
      toast.success(formik.values.id);
  };

  const submitData = async (data: EquipFormSchema) =>{
    // const config = {
    //   headers: {
    //     "Access-Control-Allow-Origin": "*",
    //     "Access-Control-Allow-Methods": "GET,PUT,POST,DELETE,PATCH,OPTIONS"
    //   }
    // };
    await axios.post(
      EQUIPMENT_ENDPOINT,
      data
    ).then((response) => {
      toast.success('Data submitted with success');
      formik.resetForm();
    }, (error) => {
      console.log(error);
      toast.error(error.response.data.error);
    }
    )
  }

  const formik = useFormik({
      initialValues: initialValues,
      validationSchema: Yup.object({
        // id: Yup.string().required('Equipment ID is required.'),
        owner: Yup.string().required('An owner institute is required.'),
        location: Yup.string().required('An equipment location is required.'),
        status: Yup.string().required('Equipment status is required.')
      }),
      onSubmit: submitData
  });

  const isFormFieldInvalid = (name: keyof EquipFormSchema) => !!(formik.touched[name] && formik.errors[name]);

  const getFormErrorMessage = (name: keyof EquipFormSchema) => {
      return isFormFieldInvalid(name) ? formik.errors[name] : null;
  };

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
              errorText={getFormErrorMessage}
              isValid={isFormFieldInvalid}/>
            <FormDropdown
              id='owner'
              name='Owner'
              options={ownerOptions}
              required
              value={formik.values.owner}
              help='Institute that owns the equipment'
              onChange={(e) => {
                formik.setFieldValue('owner', e.value);
              }}
              errorText={getFormErrorMessage}
              isValid={isFormFieldInvalid}/>
            <FormDropdown
              id='location'
              name='Location'
              options={locationOptions}
              required
              value={formik.values.location}
              help='Current equipment location'
              onChange={(e) => {
                formik.setFieldValue('location', e.value);
              }}
              errorText={getFormErrorMessage}
              isValid={isFormFieldInvalid}/>
            <FormDropdown
              id='status'
              name='Status'
              options={statusOptions}
              required
              value={formik.values.status}
              help='Current equipment status'
              onChange={(e) => {
                formik.setFieldValue('status', e.value);
              }}
              errorText={getFormErrorMessage}
              isValid={isFormFieldInvalid}/>
            <FormDropdown
              id='detector_location'
              name='Detector Location'
              options={detectorLocations}
              value={formik.values.detector_location}
              help='Where in the detector the equipment should be.'
              onChange={(e) => {
                formik.setFieldValue('detector_location', e.value);
              }}
              errorText={getFormErrorMessage}
              isValid={isFormFieldInvalid}/>
            <FormText
              id='serial'
              name='Serial'
              value={formik.values.serial}
              help='Equipment serial number (Identification code issued by the manufacturer)'
              onChange={(e) => {
                formik.setFieldValue('serial', e.target.value);
              }}
              errorText={getFormErrorMessage}
              isValid={isFormFieldInvalid}/>
            <FormText
              id='comments'
              name='Comments'
              value={formik.values.comments}
              help='Remarks about the equipment'
              onChange={(e) => {
                formik.setFieldValue('remarks', e.target.value);
              }}
              errorText={getFormErrorMessage}
              isValid={isFormFieldInvalid}/>
            <Button type="submit" label="Submit" />
          </form>
      </div>
  )
}
