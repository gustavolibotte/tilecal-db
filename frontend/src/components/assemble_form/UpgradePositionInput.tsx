import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { InputText } from 'primereact/inputtext';
import { focusNextScanField, scanPosition } from '../../utils/assemblyScan';

interface Props {
  id: string;
  value: string;
  disabled: boolean;
  expectedPosition?: number;
  onChange: (value: string) => void;
  onValidated: (valid: boolean) => void;
}

export default function UpgradePositionInput(props: Props) {
  const [status, setStatus] = useState<'idle' | 'valid' | 'invalid'>('idle');
  const [focusVersion, setFocusVersion] = useState(0);
  useLayoutEffect(() => { if (focusVersion) focusNextScanField(props.id); }, [focusVersion]);
  const timer = useRef<ReturnType<typeof setTimeout>>();
  const latest = useRef(props); latest.current = props;
  const validate = (raw: string) => {
    clearTimeout(timer.current);
    const parsed = scanPosition(raw);
    const valid = parsed !== null && (latest.current.expectedPosition === undefined || parsed === latest.current.expectedPosition);
    setStatus(valid ? 'valid' : 'invalid'); latest.current.onValidated(valid);
    if (valid) setFocusVersion(version => version + 1);
    else (document.getElementById(latest.current.id) as HTMLInputElement)?.select();
  };
  useEffect(() => {
    clearTimeout(timer.current); setStatus('idle'); latest.current.onValidated(false);
    if (props.value && !props.disabled) timer.current = setTimeout(() => validate(props.value), 400);
    return () => clearTimeout(timer.current);
  }, [props.value, props.disabled, props.expectedPosition]);
  return <div className="field mt-3">
    <label htmlFor={props.id} className="font-bold">Upgrade Position:</label>
    <div className="p-inputgroup mt-1">
      <InputText id={props.id} data-assembly-field value={props.value} disabled={props.disabled}
        inputMode="numeric" autoComplete="off" placeholder={props.expectedPosition === undefined ? 'Scan position (1–48)' : `Scan position ${props.expectedPosition}`}
        aria-invalid={status === 'invalid'} aria-describedby={`${props.id}-feedback`}
        onChange={event => { clearTimeout(timer.current); setStatus('idle');
          props.onValidated(false); props.onChange(event.target.value); }}
        onKeyDown={event => {
          if (event.key === 'Enter' || (event.key === 'Tab' && !event.shiftKey)) {
            event.preventDefault(); event.stopPropagation(); validate(event.currentTarget.value);
          }
        }} />
      <span className="p-inputgroup-addon" role="status" aria-label={status}>
        {status === 'valid' && <i className="pi pi-check text-green-600" />}
        {status === 'invalid' && <i className="pi pi-times text-red-600" />}
      </span>
    </div>
    <small id={`${props.id}-feedback`} role="status" className="p-error block mt-1">
      {status === 'invalid' ? props.expectedPosition === undefined ? 'Enter an integer from 1 to 48.' : `Expected physical position ${props.expectedPosition}.` : ''}
    </small>
  </div>;
}
