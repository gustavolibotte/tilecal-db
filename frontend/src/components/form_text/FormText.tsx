import { ChangeEventHandler, Component } from 'react';
import { InputText } from 'primereact/inputtext';
import FormField from '../form_field/FormField';
import { classNames } from 'primereact/utils';

interface FormTextProps {
  id: string;
  name: string;
  value: any;
  help: string;
  onChange: ChangeEventHandler<HTMLInputElement>;
  errorText: string | null;
  required?: boolean;
  placeholder?: string;
}

export default class FormText extends Component<FormTextProps> {
  render() {
    return (
    <FormField
      id={this.props.id}
      name={this.props.name}
      help={this.props.help}
      errorText={this.props.errorText}
      required={this.props.required}>
      <InputText
          id={this.props.id}
          name={this.props.id}
          value={this.props.value}
          onChange={this.props.onChange}
          className={classNames({ 'p-invalid': Boolean(this.props.errorText)})}
          placeholder={this.props.placeholder}
      />
    </FormField>
    )
  }
}
