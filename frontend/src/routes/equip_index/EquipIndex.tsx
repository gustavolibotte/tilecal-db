import EquipTable from '../../components/equip_table/EquipTable';
import { useSearchParams } from 'react-router-dom';
import './equip_index.css';

export default function EquipIndex(){
  const [searchParams] = useSearchParams();

  const queryParams: Record<string, string> = {};
  searchParams.forEach((value, key) => {
    queryParams[key] = value;
  });

  return (
    <div className="col-12">
      <h1>Equipment Index</h1>
      <EquipTable queryKey={['equipment', 'get', queryParams]}/>
    </div>
  );
}