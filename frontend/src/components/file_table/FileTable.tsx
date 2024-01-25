import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { ENDPOINT } from '../../utils/DbClient';

interface FileTableProps {
  queryKey: any[];
}

export default function FileTable(props: FileTableProps) {
  const getButton = (rowData: any) => {
    return <Button label={rowData.name}/>;
  }

  const columns: ColumnMeta[] = [
    {field: 'name', header: 'Name', body: getButton},
    {field: 'description', header: 'Description'},
    {field: 'created_at', header: 'Created at'},
    {field: 'updated_at', header: 'Updated at'}
  ];
  return (
    <BaseTable
      columns={columns}
      expandField=''
      dataKey='name'
      dataKeyHeader='Name'
      fileName='file_index'
      endpoint={ENDPOINT['file']}
      queryKey={props.queryKey}/>
  )
}
