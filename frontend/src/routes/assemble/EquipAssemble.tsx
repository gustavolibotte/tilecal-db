import { useState } from "react";
import { Button } from "primereact/button";
import ButtonConfirmation from "../../components/button_confirmation/ButtonConfirmation";
import { useFormik } from "formik";
import EquipAssembleComponents from "../../components/equip_assemble_components/EquipAssembleComponents";
import SlotSearchInput from "../../components/slot_search_input/SlotSearchInput";
import { preventEnterDefault } from "../../utils/event";
import { equipIdRules } from "../../utils/validation_rules";
import * as Yup from 'yup';

interface SearchFormSchema{
  parent_id: string
}

export default function EquipAssemble() {
  const submitAction = () => {
    setShowAssemblyStatus(true);
  };

  const formik = useFormik({
    initialValues: {parent_id: ''},
    onSubmit: submitAction,
    validationSchema: Yup.object({
      parent_id: equipIdRules.required(__FIELD_REQUIRED_MSG__ )
    })
  });
  const [showAssemblyStatus, setShowAssemblyStatus] = useState(false);
  const getFormErrorMessage = (name: keyof SearchFormSchema): string | null => {
    return !!(formik.touched[name] && formik.errors[name]) ? String(formik.errors[name]) : null;
  };
  
  let assemblyComponents = <></>;
  if (showAssemblyStatus && formik.values.parent_id){
    assemblyComponents = <EquipAssembleComponents
      parentId={formik.values.parent_id}/>;
  }
  
  return (
    <div className="col-12">
      <h1>Assemble Equipment:</h1>
        <form onSubmit={formik.handleSubmit} onKeyDown={preventEnterDefault}>
        {/* Usamos align-items-center para os botões ficarem alinhados com a caixa de texto */}
        <div className="flex flex-wrap align-items-center justify-content-start gap-2">
          
          <SlotSearchInput
            id='parent_id'
            label='Search for an Equipment to assemble parts'
            value={formik.values.parent_id}
            help='Example: ABC1234'
            required
            onChange={(val) => {
              setShowAssemblyStatus(false);
              formik.setFieldValue('parent_id', val);
            }}
            errorText={getFormErrorMessage('parent_id')} 
          />

          <div className="flex gap-2 mb-2">
              <Button label="Submit" type="submit"/>
              <ButtonConfirmation 
                actionLabel="Clear"
                actionSeverity="danger"
                onConfirmation={() => {
                  formik.resetForm();
                  setShowAssemblyStatus(false);
                }}
                dialogHeader="Clear page?"
                cancelSeverity="danger"
              />
          </div>
          </div>
        </form>
      <hr/>
      {assemblyComponents}
    </div>
  );
}