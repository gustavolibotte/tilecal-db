import { capitalize } from '../../utils/string';
import { BaseTable, ExpandRowType } from '../base_table/BaseTable';
import { ColumnMeta } from '../base_table/BaseTable';
import { useState, ReactNode } from 'react';
import './equip_hist_table.css';

interface EquipHistTableProps {
    equipmentId: string | undefined;
}

export default function EquipHistTable(props: EquipHistTableProps) {
  const [expandedRows, setExpandedRows] = useState<ExpandRowType>(undefined);

  const rowExpansionTemplate = (rowData: any): ReactNode => {
    return (
      <span className="p-3">
        <h5>History Data</h5>
      </span>
    );
  }

  const onRowCollapse = () => {};
  const onRowExpand = () => {};

  const columns: ColumnMeta[] = [
    {
      field: 'row_toggle', header: 'Row Toggle',
      expander: (rowData: any) => true, 
      style: { width: '5rem' }
    },
    {
      field: 'created_at', header: 'Created At',
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
      body: (rowData: any) => {return capitalize(rowData.action)},
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    }
  ];
  return (
    <>
    <BaseTable
    expandField=''
    columns={columns}
    fileName='equip_history'
    queryKey={[
      'equipment_hist',
      'get',
      {id: props.equipmentId? props.equipmentId: null}
    ]}
    expandedRows={expandedRows}
    onRowToggle={(e) => setExpandedRows(e.data)}
    rowExpansionTemplate={rowExpansionTemplate}
    onRowExpand={onRowExpand}
    onRowCollapse={onRowCollapse}/>
  </>
  )
  }
  
