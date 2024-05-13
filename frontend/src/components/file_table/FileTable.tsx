import { BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { ColumnProps } from 'primereact/column';
import { useNavigate } from 'react-router-dom';
import { getFileViewRoute } from '../../utils/routes';
import { DataTableSortMeta } from 'primereact/datatable';
import './file_table.css';

interface FileTableProps {
  queryKey: any[];
}

export default function FileTable(props: FileTableProps) {
  const navigate = useNavigate();

  const getButton = (rowData: any) => {
    const severity = rowData.deleted_at? 'danger': undefined;
    return (
    <Button
      label={rowData.name}
      severity={severity}
      onClick={() => navigate(getFileViewRoute(rowData.id))}/>
      );
  }

  const getPath = (rowData: any) => {
    return (
      <a href={rowData.path} target="_blank">{rowData.path}</a>
    );
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
      field: 'path', header: 'Path',
      filter: true, sortable: true,
      filterPlaceholder: 'Search',
      body: getPath
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

  const multiSortMeta: DataTableSortMeta[] = [
    {
      field: 'updated_at',
      order: -1
    },
    {
      field: 'name',
      order: 1
    },
  ]

  return (
    <BaseTable
      columns={columns}
      expandField=''
      dataKey='name'
      fileName='file_index'
      sortMode='multiple'
      multiSortMeta={multiSortMeta}
      queryKey={props.queryKey} />
  )
}
