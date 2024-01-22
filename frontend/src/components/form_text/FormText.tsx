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
  errorText: CallableFunction;
  isValid: CallableFunction;
  required?: boolean;
}

export default class FormText extends Component<FormTextProps> {
  render() {
    const isValid = this.props.isValid(this.props.id);
    return (
    <FormField id={this.props.id} name={this.props.name} help={this.props.help} errorText={this.props.errorText(this.props.id)} required={this.props.required} isValid={isValid}>
      <InputText
          id={this.props.id}
          name={this.props.id}
          value={this.props.value}
          onChange={this.props.onChange}
          className={classNames({ 'p-invalid': isValid })}
      />
    </FormField>
    )
  }
}
