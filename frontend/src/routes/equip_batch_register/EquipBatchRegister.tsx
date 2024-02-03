import EquipBatchForm from '../../components/equip_batch_form/EquipBatchForm';
import './equip_batch_register.css';

export default function EquipBatchRegister() {
  return (
    <div className="col-12">
      <h1>Equipment Batch Register</h1>
      <p>"*" means the field is required</p>
      <EquipBatchForm/>
    </div>
  )
}
