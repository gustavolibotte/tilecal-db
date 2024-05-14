import { BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { ColumnProps } from 'primereact/column';
import { useNavigate } from 'react-router-dom';
import { getFileViewRoute } from '../../utils/routes';
import { DataTableSortMeta } from 'primereact/datatable';
import './related_file_table.css';
import ButtonConfirmation from '../button_confirmation/ButtonConfirmation';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { useRef } from 'react';
import { Id, toast } from 'react-toastify';
import { midTransactionToastConfig } from '../../utils/toast';
import { AxiosError } from 'axios';

interface RelatedFileTableProps {
  equipmentId: string;
}

export default function RelatedFileTable({ equipmentId }: RelatedFileTableProps) {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const queryKey = [
    'file',
    'get',
    {relatedEquipmentId: equipmentId}
  ]

  const toastId = useRef<Id>();

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
  const rmRelationMutation = useMutation({
    mutationFn: makeMutationFunction(
      'post',
      getEndpoint('file_unrelate')
    ),
    onMutate: () => {
      toastId.current = toast.info('Removing relation.', midTransactionToastConfig);
    },
    onSuccess: () => {
      toast.dismiss(toastId.current);
      toast.success('Relation removed with success.');
    },
    onError: (error: AxiosError) => {
      console.log(error);
      toast.dismiss(toastId.current);
      toast.error(__DEFAULT_ERROR_MSG__);
    },
    onSettled: () => {
      queryClient.invalidateQueries({
        queryKey: queryKey
      });
    }
  });

  const getRemoveButton = (rowData: any) => {
    const mutationData = {
      file_name: rowData.name,
      equipment_id: equipmentId
    }
    console.log(mutationData)
    return (
      <ButtonConfirmation
        actionIcon='pi pi-trash'
        actionSeverity='danger'
        cancelSeverity='danger'
        dialogHeader="Confirm relation removal?"
        onConfirmation={()=>rmRelationMutation.mutate(mutationData)}/>
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
      queryKey={queryKey} />
  )
}
