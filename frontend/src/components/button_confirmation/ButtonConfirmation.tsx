import { Button } from 'primereact/button';
import { ReactNode, useState } from 'react';
import './button_confirmation.css';
import { Dialog } from 'primereact/dialog';

type Severity = "secondary" | "success" | "info" | "warning" | "danger" | "help" | undefined;

interface ButtonConfirmationProps{
  actionLabel?: string;
  actionSeverity?: Severity;
  onConfirmation: () => void;
  dialogHeader?: string;
  dialogTemplate?: () => ReactNode;
  dialogStyle?: any;
  cancelSeverity?: Severity;
  confirmSeverity?: Severity;
}


export default function ButtonConfirmation(props: ButtonConfirmationProps) {
  const [visibleDialog, setVisibleDialog] = useState(false);

  const confirmationAction = () => {
    props.onConfirmation();
    setVisibleDialog(false);
  }

  return (
    <>
      <Button type="button" label={props.actionLabel} severity={props.actionSeverity}
        onClick={() => setVisibleDialog(true)}/>
      <Dialog header={props.dialogHeader? props.dialogHeader: 'Confirm operation'}
        visible={visibleDialog}  style={props.dialogStyle}
        onHide={() => {setVisibleDialog(false)}}>
        {props.dialogTemplate && props.dialogTemplate()}
          <div className="flex flex-row-reverse flex-wrap gap-2">
            <Button type='button' label='Confirm' severity={props.confirmSeverity}
                onClick={confirmationAction}/>
            <Button type='button' label='Cancel' severity={props.cancelSeverity}
                onClick={()=> setVisibleDialog(false)}/>
          </div>
      </Dialog>
    </>
  );
}
