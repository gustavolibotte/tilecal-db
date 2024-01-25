import EquipTable from '../../components/equip_table/EquipTable';
import './equip_index.css';

export default function EquipIndex(){
  return (
    <>
      <h1>Equipment Index</h1>
      <EquipTable queryKey={['equipment', 'get', {}]}/>
    </>
  );
}
