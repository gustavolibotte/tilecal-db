import { Checkbox } from 'primereact/checkbox';

export default function FuseSelection({value, onChange, disabled = false}: {
  value: boolean | null; onChange: (value: boolean | null) => void; disabled?: boolean;
}) {
  return <fieldset className="field border-none p-0 mb-4" disabled={disabled}>
    <legend className="font-bold mb-2">FUSES is removed?</legend>
    <div className="flex gap-4">
      {([true, false] as const).map(option => <div key={String(option)} className="flex align-items-center gap-2">
        <Checkbox inputId={`fuses-removed-${option}`} checked={value === option} disabled={disabled}
          onChange={() => onChange(value === option ? null : option)} />
        <label htmlFor={`fuses-removed-${option}`}>{option ? 'Yes' : 'No'}</label>
      </div>)}
    </div>
  </fieldset>;
}
