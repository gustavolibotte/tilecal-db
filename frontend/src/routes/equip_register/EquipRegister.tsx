import { useParams } from 'react-router-dom';
import EquipForm from '../../components/equip_form/EquipForm';
import PmtForm from '../../components/pmt_form/PmtForm';
import './equip_register.css';

export default function EquipRegister() {
  let { typeName } = useParams();
  typeName = typeName? typeName: '';
  const form = (typeName == 'PMT')? <PmtForm/>: <EquipForm typeName={typeName}/>;
  return (
    <div>
      <h1>{`${typeName} Register`}</h1>
      <p>"*" means the field is required</p>
      { form };
    </div>
  )
}
