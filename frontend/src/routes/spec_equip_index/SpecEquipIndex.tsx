import { useParams } from 'react-router-dom';
import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import './spec_equip_index.css';
import { ENDPOINT } from '../../utils/DbClient';

export default function SpecEquipIndex(){

  let { typeName } = useParams();
  typeName = typeName? typeName: '';

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
      <h1>{`${typeName} Index`}</h1>
      <BaseTable
      expandField='type_params'
      columns={columns}
      dataKey='id'
      dataKeyHeader='ID'
      fileName='equip_index'
      endpoint={ENDPOINT['equipment']}
      queryKey={['equipment', 'get', {type_name: typeName}]}/>
    </>
  );
}
