import { Button } from "primereact/button";
import { useUser } from "../../utils/user"
import { Dialog } from "primereact/dialog";
import { useState } from "react";
import AssembleForm from "../assemble_form/AssembleForm";

interface AssembleComponentProps {
  disable?: boolean;
  equipId: string;
  typeName: string;
}

export default function AssembleComponent(props: AssembleComponentProps) {
  const user = useUser();
  const [visibleAssembleForm, setvisibleAssembleForm] = useState(false);
  if(user.canEdit() && (!props.disable) && props.equipId && props.typeName){
    return (
    <>
      <Button type='button' label='Assemble equipment'
        onClick={() => setvisibleAssembleForm(true)}/>
      <Dialog header='Assemble Equipment' visible={visibleAssembleForm}
        style={{ width: '50vw' }}
        onHide={() => setvisibleAssembleForm(false)}>
        <AssembleForm 
          parentId={props.equipId}
          parentTypeName={props.typeName}/>
      </Dialog>
    </>
    );
  }

  return (<></>);
}
