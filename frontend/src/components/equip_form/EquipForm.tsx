
import { useFormik } from 'formik';
import { Button } from 'primereact/button';
import { toast } from 'react-toastify';
import * as Yup from 'yup';
import FormText from '../form_text/FormText';

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

    const formik = useFormik({
        initialValues: initialValues,
        validationSchema: Yup.object({
          id: Yup.string().required('Equipment ID is required.'),
          owner: Yup.string().required('An owner institute is required.'),
          location: Yup.string().required('An equipment location is required.'),
          status: Yup.string().required('Equipment status is required.')
        }),
        onSubmit: (data) => {
            data && show();
            formik.resetForm();
        }
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
              <FormText
                id='owner'
                name='Owner'
                required
                value={formik.values.owner}
                help='Institute that owns the equipment'
                onChange={(e) => {
                  formik.setFieldValue('owner', e.target.value);
                }}
                errorText={getFormErrorMessage}
                isValid={isFormFieldInvalid}/>
              <FormText
                id='location'
                name='Location'
                required
                value={formik.values.location}
                help='Current equipment location'
                onChange={(e) => {
                  formik.setFieldValue('location', e.target.value);
                }}
                errorText={getFormErrorMessage}
                isValid={isFormFieldInvalid}/>
              <FormText
                id='status'
                name='Status'
                required
                value={formik.values.status}
                help='Current equipment status'
                onChange={(e) => {
                  formik.setFieldValue('status', e.target.value);
                }}
                errorText={getFormErrorMessage}
                isValid={isFormFieldInvalid}/>
              <FormText
                id='detector_location'
                name='Detector Location'
                value={formik.values.detector_location}
                help='Where in the detector the equipment should be.'
                onChange={(e) => {
                  formik.setFieldValue('serial', e.target.value);
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
        