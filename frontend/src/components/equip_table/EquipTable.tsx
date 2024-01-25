import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { ENDPOINT } from '../../utils/DbClient';
import { useNavigate } from 'react-router-dom';
import { getEquipviewRoute } from '../../utils/routes';

interface EquipTableProps {
  queryKey: any[];
}

export default function EquipTable(props: EquipTableProps) {
  const navigate = useNavigate();

  const getButton = (rowData: any) => {
    return <Button label={rowData.id} onClick={() => navigate(getEquipviewRoute(rowData.id))}/>;
  }

  const columns: ColumnMeta[] = [
    {field: 'id', header: 'ID', body: getButton},
    {field: 'type_name', header: 'Type'},
    {field: 'status_name', header: 'Status'},
    {field: 'location_name', header: 'Location'},
    {field: 'owner_name', header: 'Owner'},
    {field: 'detector_location_name', header: 'Detector Location'},
    {field: 'comments', header: 'Comments'},
    {field: 'created_at', header: 'Created at'},
    {field: 'updated_at', header: 'Updated at'}
  ]
  return (
    <>
    <BaseTable
    expandField=''
    columns={columns}
    dataKey='id'
    dataKeyHeader='ID'
    fileName='equip_index'
    endpoint={ENDPOINT['equipment']}
    queryKey={props.queryKey}/>
  </>
  )
}
