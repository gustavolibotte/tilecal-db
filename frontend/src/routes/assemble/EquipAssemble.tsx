import { useState } from "react";
import AssembleForm from "../../components/assemble_form/AssembleForm";
import RelatedEquipTable from "../../components/related_equip_table/RelatedEquipTable";
import SearchField from "../../components/search_field/SearchField";
import { Button } from "primereact/button";
import ButtonConfirmation from "../../components/button_confirmation/ButtonConfirmation";

export default function EquipAssemble() {
  const [parentId, setParentId] = useState('');
  const [showAssemblyStatus, setShowAssemblyStatus] = useState(false);
  
  let assemblyStatusComponents = <></>;
  if (showAssemblyStatus){
    assemblyStatusComponents =(
      <>
        <h2>Assemble new equipment into {parentId}</h2>
        <AssembleForm parentId={parentId} parentTypeName="PMT Block"/>
        <hr/>
        <h2>Already Assembled into {parentId}</h2>
        <RelatedEquipTable equipmentId={parentId}/>
      </>);
  }
  
  return (
    <div className="col-12">`
      <h1>Assemble Equipment:</h1>
      <div className="flex flex-wrap align-items-center justify-content gap-2">
        <SearchField onChange={(e) => setParentId(e.target.value)}/>
        <Button label="Submit" onClick={() => setShowAssemblyStatus(true)}/>
        <ButtonConfirmation 
          actionLabel="Clear"
          actionSeverity="danger"
          onConfirmation={() => {
            setParentId('');
            setShowAssemblyStatus(false);
          }}
          dialogHeader="Clear page?"
          cancelSeverity="danger"/>
      </div>
      <hr/>
      {assemblyStatusComponents}
    </div>
  );
}
