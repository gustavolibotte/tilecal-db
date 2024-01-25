import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { ENDPOINT } from '../../utils/DbClient';

interface EquipTableProps {
  queryKey: any[];
}

export default function EquipTable(props: EquipTableProps) {
  const getButton = (rowData: any) => {
    return <Button label={rowData.id}/>;
  }

  const columns: ColumnMeta[] = [
    {field: 'id', header: 'ID', body: getButton},
    {field: 'type_name', header: 'Type'},
    {field: 'location_name', header: 'Location'},
    {field: 'detector_location_name', header: 'Detector Location'},
    {field: 'status_name', header: 'Status'},
    {field: 'owner_name', header: 'Owner'},
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
