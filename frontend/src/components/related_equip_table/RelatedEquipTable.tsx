import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { useNavigate } from 'react-router-dom';
import { getEquipviewRoute } from '../../utils/routes';
import { snakeToSpaced } from '../../utils/string';
import { DataTableSortMeta } from 'primereact/datatable';
import './related_equip_table.css';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { Id, toast } from 'react-toastify';
import { AxiosError } from 'axios';
import ButtonConfirmation from '../button_confirmation/ButtonConfirmation';
import { midTransactionToastConfig } from '../../utils/toast';
import { useRef } from 'react';

interface RelatedEquipTableProps {
    equipmentId: string | undefined;
}

export default function RelatedEquipTable(props: RelatedEquipTableProps) {
  const navigate = useNavigate();

  const relationTypeTemplate = (rowData: any) => {
    if (rowData.relation_type == 'parent'){
      return <p>Assembled into</p>;
    } else if (rowData.relation_type == 'child') {
      return <p> Assembled</p>;
    } else {
      return <p>{snakeToSpaced(rowData.relation_type? rowData.relation_type: '')}</p>;
    }
  }

  const slotTemplate = (rowData: any) => {
    return <p>{snakeToSpaced(rowData.slot_name? rowData.slot_name: '')}</p>;
  }

  const queryKey = [
    'equipment',
    'get',
    {relatedEquipmentId: props.equipmentId? props.equipmentId: null}
  ];

  const queryClient = useQueryClient();

  const toastId = useRef<Id>();

  const rmRelationMutation = useMutation({
    mutationFn: makeMutationFunction(
      'post',
      getEndpoint('equipment_disassemble')
    ),
    onMutate: () => {
      toastId.current = toast.info('Removing relation.', midTransactionToastConfig);
    },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      toast.success('Relation removed with success.');
      queryClient.invalidateQueries({
        queryKey: queryKey
      })
    },
    onError: (error: AxiosError) => {
      console.log(error);
      toast.dismiss(toastId.current);
      toast.error('An error has occured.');
    }
  });

  const getButton = (rowData: any) => {
    return <Button label={rowData.id} onClick={() => navigate(getEquipviewRoute(rowData.id))}/>;
  }

  const getRemoveButton = (rowData: any) => {
    let mutationData: any;
    switch (rowData.relation_type){
      case 'parent':
        mutationData = {
          child_id: props.equipmentId,
          parent_id: rowData.id
        }
        break;
      case 'child':
        mutationData = {
          child_id: rowData.id,
          parent_id: props.equipmentId
        }
        break;
      default:
        return (
        <ButtonConfirmation
          actionIcon='pi pi-trash'
          actionSeverity='danger'
          cancelSeverity='danger'
          dialogHeader="Can't remove the relation, click on any button to exit."
          onConfirmation={()=>{}}
        />);
    }
    return (
      <ButtonConfirmation
        actionIcon='pi pi-trash'
        actionSeverity='danger'
        cancelSeverity='danger'
        dialogHeader='Confirm relation removal?'
        onConfirmation = {() => {
          rmRelationMutation.mutate(mutationData)
        }}
      />
    );
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
    },
    {
      field: 'none', header: 'Remove', body: getRemoveButton,
      filter: false, sortable: false,
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
    sortMode='multiple'
    multiSortMeta={multiSortMeta}
    queryKey={queryKey}/>
  </>
  )
}
