import { BaseTable, ExpandRowType, ColumnMeta } from '../base_table/BaseTable';
import { useState, ReactNode } from 'react';
import EquipInfo from '../equip_info/EquipInfo';
import './equip_hist_table.css';
import { DataTableSortMeta } from 'primereact/datatable';
import { HistAction, HistData } from '../hist/HistAction';

interface EquipHistTableProps {
  equipmentId: string;
}


export default function EquipHistTable(props: EquipHistTableProps) {
  const [expandedRows, setExpandedRows] = useState<ExpandRowType>(undefined);

  const rowExpansionTemplate = (rowData: HistData): ReactNode => {

    switch (rowData.hist_type) {
      case 'equip':
        return (
          <EquipInfo equipData={rowData} hist/>
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
      expander: (rowData: any) => rowData.hist_type == 'equip',
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
        fileName='equip_history'
        queryKey={[
          'equipment_hist',
          'get',
          { id: props.equipmentId ? props.equipmentId : null }
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

