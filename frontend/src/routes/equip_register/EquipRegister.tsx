import { useParams } from 'react-router-dom';
import EquipForm from '../../components/equip_form/EquipForm';
import './equip_register.css';

export default function EquipRegister() {
  let { typeName } = useParams();
  typeName = typeName? typeName: '';
  return (
    <div>
      <h1>{`${typeName} Register`}</h1>
      <EquipForm/>
    </div>
  )
}
