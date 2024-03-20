import { InputText } from "primereact/inputtext";
import { ChangeEventHandler } from "react";

interface SearchFieldProps {
  onChange: ChangeEventHandler<HTMLInputElement> | undefined
}

export default function SearchField({onChange}: SearchFieldProps) {
  return (
    <span className="p-input-icon-left">
    <i className="pi pi-search" />
    <InputText
      placeholder="Search"
      onChange={onChange}/>
  </span>
  );
}
