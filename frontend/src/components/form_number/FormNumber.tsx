import { Component } from 'react';
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

export default class FormNumber extends Component<FormTextProps> {
  render() {
    return (
    <FormField
      id={this.props.id}
      name={this.props.name}
      help={this.props.help}
      errorText={this.props.errorText}
      required={this.props.required}>
      <InputNumber
          id={this.props.id}
          name={this.props.id}
          value={this.props.value}
          onChange={this.props.onChange}
          className={classNames({ 'p-invalid': Boolean(this.props.errorText) })}
      />
    </FormField>
    )
  }
}
