import { useState, ReactNode } from 'react';
import './file_hist_table.css';
import { ExpandRowType, BaseTable, ColumnMeta } from '../base_table/BaseTable';
import FileInfo from '../file_info/FileInfo';
import { HistData, HistAction } from '../hist/Hist';
import { DataTableSortMeta } from 'primereact/datatable';

interface FileHistTableProps {
    fileId: string
}

export default function FileHistTable(props: FileHistTableProps) {

  const [expandedRows, setExpandedRows] = useState<ExpandRowType>(undefined);

  const rowExpansionTemplate = (rowData: HistData): ReactNode => {

    switch (rowData.hist_type) {
      case 'file':
        return (
          <FileInfo fileData={rowData} hist/>
        );
      default:
        return (
          <h5> Could not render this history log.</h5>
        );
    }
  }
  const onRowCollapse = () => { };
  const onRowExpand = () => { };

  const columns: ColumnMeta[] = [
    {
      field: 'row_toggle', header: 'Toggle',
      expander: (rowData: any) => rowData.hist_type == 'file',
      style: { width: '5rem' }
    },
    {
      field: 'created_at', header: 'Created At',
      className: 'font-bold',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'user', header: 'User',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'action', header: 'Action',
      body: HistAction,
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    }
  ];

  const multiSortMeta: DataTableSortMeta[] = [
    {
      field: 'created_at',
      order: -1
    }
  ]

  return (
    <>
      <BaseTable
        expandField=''
        columns={columns}
        fileName='file_history'
        queryKey={[
          'file_hist',
          'get',
          { id: props.fileId ? props.fileId : null }
        ]}
        expandedRows={expandedRows}
        onRowToggle={(e) => setExpandedRows(e.data)}
        rowExpansionTemplate={rowExpansionTemplate}
        onRowExpand={onRowExpand}
        onRowCollapse={onRowCollapse}
        sortMode='multiple'
        multiSortMeta={multiSortMeta}/>
    </>
  )
}
