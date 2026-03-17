import { useParams } from 'react-router-dom';
import EquipForm from '../../components/equip_form/EquipForm';
import PmtForm from '../../components/pmt_form/PmtForm';
import DaughterboardForm from '../../components/daughterboard_form/DaughterboardForm';
import './equip_register.css';

export default function EquipRegister() {
  let { typeName } = useParams();
  typeName = typeName ? typeName : '';
  
  let form;
  if (typeName === 'PMT') {
    form = <PmtForm />;
  } else if (typeName === 'Daughterboard') {
    form = <DaughterboardForm typeName={typeName} />;
  } else {
    form = <EquipForm typeName={typeName} />;
  }

  return (
    <div className="col-12">
      <h1>{`${typeName} Register`}</h1>
      <p>"*" means the field is required</p>
      { form }
    </div>
  )
}