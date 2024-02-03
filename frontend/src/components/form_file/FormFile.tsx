import { InputHTMLAttributes } from 'react';
import FormField from '../form_field/FormField';
import './form_file.css';

interface FormFileProps {
  id: string;
  name: string;
  help: string;
  onChange: (e: InputHTMLAttributes<HTMLInputElement>) => void;
  errorText: string | null;
  required?: boolean;
}

export default function FormFile(props: FormFileProps) {
  return (
    <FormField
      id={props.id}
      name={props.name}
      help={props.help}
      errorText={props.errorText}
      required={props.required}>
      <input
        className='p-button'
        id={props.id}
        name={props.name}
        type="file"
        onChange={props.onChange} />
    </FormField>
    )
}
