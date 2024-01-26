import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { useNavigate } from 'react-router-dom';
import { getEquipviewRoute } from '../../utils/routes';
import { snakeToSpaced } from '../../utils/string';
import { DataTableSortMeta } from 'primereact/datatable';

interface RelatedEquipTableProps {
    equipmentId: string | undefined;
}

export default function RelatedEquipTable(props: RelatedEquipTableProps) {
  const navigate = useNavigate();

  const relationTypeTemplate = (rowData: any) => {
    return <p>{snakeToSpaced(rowData.relation_type)}</p>;
  }

  const slotTemplate = (rowData: any) => {
    return <p>{snakeToSpaced(rowData.slot_name)}</p>;
  }

  const getButton = (rowData: any) => {
    return <Button label={rowData.id} onClick={() => navigate(getEquipviewRoute(rowData.id))}/>;
  }
  const columns: ColumnMeta[] = [
    {
      field: 'relation_type', header: 'Relation Type', body: relationTypeTemplate,
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
    {
      field: 'slot', header: 'Slot', body: slotTemplate,
      filter: true, sortable: true,
      filterPlaceholder: 'Search'
    },
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
    multiSortMeta={multiSortMeta}
    queryKey={[
      'equipment',
      'get',
      {relatedEquipmentId: props.equipmentId? props.equipmentId: null}
    ]}/>
  </>
  )
}
