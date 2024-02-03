import { InputNumber, InputNumberChangeEvent } from 'primereact/inputnumber';
import FormField from '../form_field/FormField';
import { classNames } from 'primereact/utils';

interface FormTextProps {
  id: string;
  name: string;
  value: any;
  help: string;
  onChange: (e: InputNumberChangeEvent) => void;
  errorText: string | null;
  required?: boolean;
}

export default function FormNumber(props: FormTextProps){
  return (
  <FormField
    id={props.id}
    name={props.name}
    help={props.help}
    errorText={props.errorText}
    required={props.required}>
    <InputNumber
        id={props.id}
        name={props.id}
        value={props.value}
        onChange={props.onChange}
        className={classNames({ 'p-invalid': Boolean(props.errorText) })}
    />
  </FormField>
  )
}
