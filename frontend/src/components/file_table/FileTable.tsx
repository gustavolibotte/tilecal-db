import { BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { ColumnProps } from 'primereact/column';

interface FileTableProps {
  queryKey: any[];
}

export default function FileTable(props: FileTableProps) {
  const getButton = (rowData: any) => {
    return <Button label={rowData.name} />;
  }

  const columns: ColumnProps[] = [
    {
      field: 'name', header: 'Name', body: getButton,
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'description', header: 'Description',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'created_at', header: 'Created At',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'updated_at', header: 'Updated At',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    }
  ];

  return (
    <BaseTable
      columns={columns}
      expandField=''
      dataKey='name'
      fileName='file_index'
      queryKey={props.queryKey} />
  )
}
