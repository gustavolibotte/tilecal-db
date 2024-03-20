import { Component, ReactNode } from 'react';

interface FormFieldProps {
  children: ReactNode;  // Exists so TS does not give errors
  id: string;
  name: string;
  help: string;
  required?: boolean;
  errorText: string | null;
}

export default class FormField extends Component<FormFieldProps> {
  render() {
    let fieldText = this.props.name;
    if (this.props.required){
      fieldText += '*:';
    } else{
      fieldText += ':';
    }
    return (
      <div className="flex flex-column gap-2">
        <label htmlFor={this.props.id}>{fieldText}</label>
        {this.props.children}
        <small id={`${this.props.id}-help`}>
          {this.props.help}
        </small>
        {this.props.errorText && <small id={`${this.props.id}-error`} className="p-error">{this.props.errorText}</small>}
      </div>
    )
  };
}
