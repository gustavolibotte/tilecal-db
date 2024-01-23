import { Component } from 'react';
import { Dropdown, DropdownChangeEvent } from 'primereact/dropdown';
import FormField from '../form_field/FormField';
import { classNames } from 'primereact/utils';

import './form_dropdown.css';

interface OptionSchema {
    name: string;
    value: any;
}

interface FormDropdownProps {
  id: string;
  name: string;
  value: any;
  help: string;
  onChange: (event: DropdownChangeEvent) => void;
  errorText: string | null;
  required?: boolean;
  options: OptionSchema[]
}

export default class FormDropdown extends Component<FormDropdownProps> {
  render() {
    return (
      <FormField
       id={this.props.id}
       name={this.props.name}
       help={this.props.help}
       errorText={this.props.errorText}
       required={this.props.required}>
        <Dropdown
          id={this.props.id}
          name={this.props.id}
          value={this.props.value}
          onChange={this.props.onChange}
          className={classNames({ 'p-invalid': Boolean(this.props.errorText) })}
          options={this.props.options}
          optionLabel='name'
          optionValue='value'
          placeholder={`Select a ${this.props.name}`}
          filter
          showClear
        />
      </FormField>
    )
  }
}
