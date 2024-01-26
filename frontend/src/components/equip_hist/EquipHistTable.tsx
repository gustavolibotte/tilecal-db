import { capitalize } from '../../utils/string';
import { BaseTable, ExpandRowType } from '../base_table/BaseTable';
import { ColumnMeta } from '../base_table/BaseTable';
import { useState, ReactNode } from 'react';
import EquipInfo from '../equip_info/EquipInfo';
import './equip_hist_table.css';
import { Link } from 'react-router-dom';
import { getEquipviewRoute, getFileViewRoute } from '../../utils/routes';

interface EquipHistTableProps {
  equipmentId: string;
}

interface HistData {
  hist_type: string
  [otherKeys: string | number | symbol]: any
}

export default function EquipHistTable(props: EquipHistTableProps) {
  const [expandedRows, setExpandedRows] = useState<ExpandRowType>(undefined);

  const rowExpansionTemplate = (rowData: HistData): ReactNode => {

    switch (rowData.hist_type) {
      case 'equip':
        return (
          <EquipInfo equipData={rowData} />
        );
      default:
        return (
          <h5> Could not render this history log.</h5>
        );
    }
  }

  const actionTemplate = (rowData: HistData): ReactNode => {
    const histType = rowData.hist_type;
    const action = rowData.action;
    if (histType == 'file_rel' && action == 'insert') {
      return (
        <p>
          Links file <Link to={getFileViewRoute(rowData.file_id)}> {rowData.file_name} </Link> to <Link to={getEquipviewRoute(props.equipmentId)}>{props.equipmentId}</Link>
        </p>
      );
    } else if (histType == 'file_rel' && action == 'delete') {
      return (
        <p>
          Unlinks file <Link to={getFileViewRoute(rowData.file_id)}>{rowData.file_name} </Link> to <Link to={getEquipviewRoute(props.equipmentId)}>{props.equipmentId}</Link>
        </p>
      );
    } else if (histType == 'parent_rel' && action == 'insert') {
      return (
        <p>
          Assembles <Link to={getEquipviewRoute(rowData.child_equip_id)}>{rowData.child_equip_id}</Link> into <Link to={getEquipviewRoute(rowData.parent_equip_id)}> {rowData.parent_equip_id} </Link>
        </p>
      );
    } else if (histType == 'parent_rel' && action == 'delete') {
      return (
        <p>
          Disassembles <Link to={getEquipviewRoute(rowData.child_equip_id)}>{rowData.child_equip_id}</Link> into <Link to={getEquipviewRoute(rowData.parent_equip_id)}> {rowData.parent_equp_id} </Link>
        </p>
      );
    } else{
      return capitalize(rowData.action);
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
      body: actionTemplate,
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
          { id: props.equipmentId ? props.equipmentId : null }
        ]}
        expandedRows={expandedRows}
        onRowToggle={(e) => setExpandedRows(e.data)}
        rowExpansionTemplate={rowExpansionTemplate}
        onRowExpand={onRowExpand}
        onRowCollapse={onRowCollapse}
        sortField="created_at"
        sortOrder={-1} />
    </>
  )
}

