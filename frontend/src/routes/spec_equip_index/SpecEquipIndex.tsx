import { useParams } from 'react-router-dom';
import EquipTable from '../../components/equip_table/EquipTable';
import './spec_equip_index.css';

export default function SpecEquipIndex(){

  let { typeName } = useParams();
  typeName = typeName? typeName: '';
  return (
    <>
      <h1>{typeName} Index</h1>
      <EquipTable queryKey={['equipment', 'get', {type_name: typeName}]}/>
    </>
  );
}
