import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { useNavigate } from 'react-router-dom';
import { getEquipviewRoute } from '../../utils/routes';
import { DataTableSortMeta } from 'primereact/datatable';

interface EquipTableProps {
  queryKey: any[];
}

export default function EquipTable(props: EquipTableProps) {
  const navigate = useNavigate();

  const getButton = (rowData: any) => {
    return <Button label={rowData.id} onClick={() => navigate(getEquipviewRoute(rowData.id))}/>;
  }

  const columns: ColumnMeta[] = [
    {
      field: 'id', header: 'ID', body: getButton,
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'type_name', header: 'Type',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'status_name', header: 'Status',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'location_name', header: 'Location',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'owner_name', header: 'Owner',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'detector_location_name', header: 'Detector Location',
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'comments', header: 'Comments',
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

  const multiSortMeta: DataTableSortMeta[] = [
    {
      field: 'updated_at',
      order: -1
    },
    {
      field: 'id',
      order: 1
    },
  ]

  return (
    <>
    <BaseTable
    expandField=''
    columns={columns}
    dataKey='id'
    fileName='equip_index'
    queryKey={props.queryKey}
    multiSortMeta={multiSortMeta}/>
  </>
  )
}
