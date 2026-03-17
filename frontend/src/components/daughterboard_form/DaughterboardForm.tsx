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
  fpgaDnaRules,
  daughterboardSerialRules
} from '../../utils/validation_rules';
import * as Yup from 'yup';

interface DaughterboardFormSchema {
  id: string;
  owner_mne: string;
  location_name: string;
  status_name: string;
  serial: string;
  comments: string;
  type_name?: string;
  fpga_dna_a: string;
  fpga_dna_b: string;
  serial_number: string;
}

interface EquipFormProps {
  equipId?: string;
  typeName: string;
  onSuccess?: () => void;
  isEdit?: boolean;
}

export default function DaughterboardForm({ equipId, typeName, onSuccess, isEdit }: EquipFormProps) {

  const locationQuery: any = useQuery({ queryKey: ['location', 'get', {}], queryFn: defaultQueryFunction });
  const instituteQuery: any = useQuery({ queryKey: ['institute', 'get', {}], queryFn: defaultQueryFunction });
  const statusQuery: any = useQuery({ queryKey: ['status', 'get', {}], queryFn: defaultQueryFunction });

  const { data: equipData, isPending: isEquipPending } = useQuery({
    queryKey: ['equipment', 'get', { id: equipId }],
    queryFn: defaultQueryFunction,
    enabled: !!equipId,
  });

  let initialValues: DaughterboardFormSchema = {
    id: '', owner_mne: '', location_name: '', status_name: '', serial: '', comments: '',
    fpga_dna_a: '', fpga_dna_b: '', serial_number: ''
  };

  const toastId = useRef<Id>();

  const equipMutation = useMutation({
    mutationFn: makeMutationFunction(equipId ? 'put' : 'post', getEndpoint('equipment')),
    onMutate: () => { toastId.current = toast.info(equipId ? 'Editing equipment.' : 'Registering equipment.', midTransactionToastConfig); },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      toast.success(equipId ? 'Equipment edited with success.' : 'Equipment registered with success.');
      if (onSuccess) onSuccess();
      if (!equipId) formik.resetForm();
    },
    onError: (error: AxiosError) => {
      toast.dismiss(toastId.current);
      const responseData: any = error?.response?.data;
      if (responseData.message === "Invalid data.") {
        Object.keys(responseData.data).forEach((key: string) => {
          formik.setFieldError(key as keyof DaughterboardFormSchema, responseData.data[key]);
        });
      } else {
        toast.error('An error has occurred.');
      }
    },
  });

  const submitData = async (data: DaughterboardFormSchema) => {
    data['type_name'] = typeName;
    if (equipId) data['id'] = equipId;
    equipMutation.mutate(data);
  };

  const validationSchema: any = {
    serial: serialRules,
    comments: commentsRules,
    fpga_dna_a: fpgaDnaRules,
    fpga_dna_b: fpgaDnaRules,
    serial_number: daughterboardSerialRules,
  };

  if (!isEdit) {
    validationSchema['owner_mne'] = Yup.string().required("Field is required.");
    validationSchema['location_name'] = Yup.string().required("Field is required.");
    validationSchema['status_name'] = Yup.string().required("Field is required.");
  }

  const formik = useFormik({
    initialValues: initialValues,
    onSubmit: submitData,
    validationSchema: Yup.object(validationSchema)
  });

  useEffect(() => {
    const responseData: any = equipData?.data || [];
    const equipArray = (!Array.isArray(responseData) && responseData.data !== undefined) ? responseData.data : responseData;
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
        fpga_dna_a: equipment.type_params?.fpga_dna_a || '',
        fpga_dna_b: equipment.type_params?.fpga_dna_b || '',
        serial_number: equipment.type_params?.serial_number || '',
      });
    }
  }, [equipData]);

  const getFormErrorMessage = (name: keyof DaughterboardFormSchema): string | null => {
    return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };

  const isPending = (locationQuery.isPending || instituteQuery.isPending || statusQuery.isPending || (isEdit && isEquipPending));
  if (isPending) return <p>Loading...</p>;
  if (locationQuery.isError || instituteQuery.isError || statusQuery.isError) return <p>Error loading dependencies.</p>;

  const locationNameOptions = locationQuery.data.data.map((l: any) => ({ name: l.name, value: l.name }));
  const ownerMneOptions = instituteQuery.data.data.map((i: any) => ({ name: `${i.mne} - ${i.name}`, value: i.mne }));
  const statusNameOptions = statusQuery.data.data.map((s: any) => ({ name: `${s.name} - ${s.description}`, value: s.name }));

  return (
    <div className="card flex justify-content-center flex-column gap-2">
      <form onSubmit={formik.handleSubmit}>
        <div className='mt-3'>
          <FormDropdown id='owner_mne' name='Owner' options={ownerMneOptions} required={!isEdit} value={formik.values.owner_mne} help='Institute that owns the equipment' onChange={(e) => formik.setFieldValue('owner_mne', e.value)} errorText={getFormErrorMessage('owner_mne')} />
        </div>
        <div className='mt-3'>
          <FormDropdown id='location_name' name='Location' options={locationNameOptions} required={!isEdit} value={formik.values.location_name} help='Current equipment location' onChange={(e) => formik.setFieldValue('location_name', e.value)} errorText={getFormErrorMessage('location_name')} />
        </div>
        <div className='mt-3'>
          <FormDropdown id='status_name' name='Status' options={statusNameOptions} required={!isEdit} value={formik.values.status_name} help='Current equipment status' onChange={(e) => formik.setFieldValue('status_name', e.value)} errorText={getFormErrorMessage('status_name')} />
        </div>
        
        <div className='mt-3'>
          <FormText id='fpga_dna_a' name='Kintex ID (FPGA DNA) A' value={formik.values.fpga_dna_a} help='24-character alphanumeric Kintex ID' onChange={(e) => formik.setFieldValue('fpga_dna_a', e.target.value)} errorText={getFormErrorMessage('fpga_dna_a')} />
        </div>
        <div className='mt-3'>
          <FormText id='fpga_dna_b' name='Kintex ID (FPGA DNA) B' value={formik.values.fpga_dna_b} help='24-character alphanumeric Kintex ID' onChange={(e) => formik.setFieldValue('fpga_dna_b', e.target.value)} errorText={getFormErrorMessage('fpga_dna_b')} />
        </div>
        <div className='mt-3'>
          <FormText id='serial_number' name='Board Serial Number' value={formik.values.serial_number} help='7-digit format: 11bbnnn' onChange={(e) => formik.setFieldValue('serial_number', e.target.value)} errorText={getFormErrorMessage('serial_number')} />
        </div>

        <div className='mt-3'>
          <FormText id='serial' name='Manufacturer Serial' required={!isEdit} value={formik.values.serial} help='Equipment identification code issued by the manufacturer' onChange={(e) => formik.setFieldValue('serial', e.target.value)} errorText={getFormErrorMessage('serial')} />
        </div>
        <div className='mt-3'>
          <FormText id='comments' name='Comments' value={formik.values.comments} help='Remarks about the equipment' onChange={(e) => formik.setFieldValue('comments', e.target.value)} errorText={getFormErrorMessage('comments')} />
        </div>
        <div className='mt-3'>
          <Button type="submit" label="Submit" />
        </div>
      </form>
    </div>
  )
}