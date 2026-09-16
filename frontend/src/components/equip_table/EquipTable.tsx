import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { useNavigate } from 'react-router-dom';
import { getEquipviewRoute } from '../../utils/routes';
import { DataTableSortMeta } from 'primereact/datatable';
import { useQuery } from '@tanstack/react-query';
import { snakeToSpaced } from '../../utils/string';
import { FilterMatchMode } from 'primereact/api';

interface EquipTableProps {
  queryKey: any[];
  typeName?: string;
}

export default function EquipTable(props: EquipTableProps) {
  const navigate = useNavigate();

  const getButton = (rowData: any) => {
    const severity = rowData.deleted_at ? 'danger' : undefined;
    return (
      <Button
        label={rowData.id}
        severity={severity}
        className="p-button-link p-0 m-0"
        onClick={() => navigate(getEquipviewRoute(rowData.id))}
      />
    );
  };

  const extraColumns: ColumnMeta[] = [];
  if (props.typeName) {
    props.queryKey[2].type_name = props.typeName;

    const equipTypeQuery: any = useQuery({
      queryKey: ['equipment_type', 'get', { type_name: props.typeName }]
    });

    if (equipTypeQuery.isError) {
      return <h4>Error Loading Equipment Types</h4>;
    } else if (equipTypeQuery.isPending) {
      return <h4>Loading...</h4>;
    }

    if (equipTypeQuery.data && equipTypeQuery.data.data && equipTypeQuery.data.data[0]) {
      const equipTypeData = equipTypeQuery.data.data[0];
      equipTypeData.params.forEach((field: string) => {
        extraColumns.push({
          field: `type_params.${field}`,
          filterField: `type_params->${field}`,
          sortField: `type_params->${field}`,
          header: snakeToSpaced(field ? field : ''),
          filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
          filterPlaceholder: 'Search'
        });
      });
    }
  }

  let columns: ColumnMeta[] = [
    {
      field: 'id', header: 'ID', body: getButton,
      filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
      filterPlaceholder: 'Search'
    },
    {
      field: 'type_name', header: 'Type',
      filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
      filterPlaceholder: 'Search'
    },
    {
      field: 'status_name', header: 'Status',
      filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
      filterPlaceholder: 'Search'
    },
    {
      field: 'location_name', header: 'Location',
      filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
      filterPlaceholder: 'Search'
    },
    {
      field: 'owner_name', header: 'Owner',
      filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
      filterPlaceholder: 'Search'
    },
    {
      field: 'detector_location_name', header: 'Detector Location',
      filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
      filterPlaceholder: 'Search'
    }
  ];

  columns = columns.concat(extraColumns);
  columns = columns.concat([
    {
      field: 'comments', header: 'Comments',
      filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
      filterPlaceholder: 'Search'
    },
    {
      field: 'created_at', header: 'Created At',
      filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
      filterPlaceholder: 'Search'
    },
    {
      field: 'updated_at', header: 'Updated At',
      filter: true, sortable: true, filterMatchMode: FilterMatchMode.CONTAINS,
      filterPlaceholder: 'Search'
    }
  ]);

  const multiSortMeta: DataTableSortMeta[] = [
    { field: 'updated_at', order: -1 },
    { field: 'id', order: 1 }
  ];

  return (
    <BaseTable
      expandField=''
      columns={columns}
      dataKey='id'
      fileName='equip_index'
      queryKey={props.queryKey}
      sortMode='multiple'
      multiSortMeta={multiSortMeta} 
    />
  );
}