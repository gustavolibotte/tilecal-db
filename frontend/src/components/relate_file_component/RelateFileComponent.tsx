import { Button } from "primereact/button";
import { useUser } from "../../utils/user"
import { Dialog } from "primereact/dialog";
import { useState } from "react";
import RelateFileForm from "../relate_file_form/RelateFileForm";

interface RelateFileComponentProps {
  disable?: boolean;
  equipmentId?: string;
}

export default function RelateFileComponent(props: RelateFileComponentProps) {
  const user = useUser();
  const [visibleForm, setvisibleForm] = useState(false);
  if(user.canEdit() && (!props.disable)){
    return (
    <>
      <Button type='button' label='Relate file'
        onClick={() => setvisibleForm(true)}/>
      <Dialog header='Relate File' visible={visibleForm}
        style={{ width: '50vw' }}
        onHide={() => setvisibleForm(false)}>
        <RelateFileForm equipmentId={props.equipmentId? props.equipmentId: ''}/>
      </Dialog>
    </>
    );
  }

  return (<></>);
}
