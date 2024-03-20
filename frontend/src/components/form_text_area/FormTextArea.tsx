import FormField from '../form_field/FormField';
import { InputTextarea } from 'primereact/inputtextarea';
import { classNames } from 'primereact/utils';
import { ChangeEventHandler } from 'react';
import './form_text_area.css';

interface FormTextAreaProps {
  id: string;
  name: string;
  value: any;
  help: string;
  onChange: ChangeEventHandler<HTMLTextAreaElement>;
  errorText: string | null;
  required?: boolean;
  placeholder?: string
}

export default function FormTextArea(props: FormTextAreaProps) {
  return (
    <FormField
      id={props.id}
      name={props.name}
      help={props.help}
      errorText={props.errorText}
      required={props.required}>
      <InputTextarea
        autoResize
        rows={5} cols={30}
        id={props.id}
        name={props.id}
        value={props.value}
        onChange={props.onChange}
        placeholder={props.placeholder}
        className={classNames({ 'p-invalid': Boolean(props.errorText) })}
      />
    </FormField>
  );
}
