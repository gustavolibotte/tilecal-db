import { ReactNode, useRef, useState } from 'react'
import { DataTable, DataTableFilterMeta } from 'primereact/datatable';
import { MultiSelect, MultiSelectChangeEvent } from 'primereact/multiselect';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';
import { FilterMatchMode } from 'primereact/api';
import { DataTableSortMeta } from 'primereact/datatable';
import { ColumnBodyOptions } from 'primereact/column';
import { toast } from 'react-toastify';
import { snakeToSpaced } from '../../utils/string';
import './base_table.css';
import { useQuery, useQueryClient } from '@tanstack/react-query';

interface TableProps {
  columns: ColumnMeta[];
  endpoint: string;
  dataKey: string;
  dataKeyHeader: string;
  fileName: string;
  expandField: string;
  queryKey: Array<any>;
};

export interface ColumnMeta {
  field: string;
  header: string;
  body?: ReactNode | ((data: any, options: ColumnBodyOptions) => React.ReactNode)
};

export function BaseTable(props: TableProps){

  const getClearFilters = (): DataTableFilterMeta => {
    let filters: DataTableFilterMeta = {};
    allColumns.forEach(
        (colMeta: ColumnMeta)  => {
            filters[colMeta.field] = {value: null, matchMode: FilterMatchMode.STARTS_WITH}
        }
    );
    return filters;
  }

  const clearFilter = (): void => {
    setFilters(getClearFilters());
  }

  const onColumnToggle = (event: MultiSelectChangeEvent): void => {
    let selectedColumns = event.value;
    let orderedSelectedColumns = allColumns.filter((col: any) => selectedColumns.some((sCol: any) => sCol.field === col.field));
    setVisibleColumns(orderedSelectedColumns);
  }

  const exportCSV = (selectionOnly: boolean): void =>{
    dt.current.exportCSV({
      selectionOnly: selectionOnly,
      filename: props.fileName
    });
  }

  const exportPdf = (): void => {
    const exportColumns = visibleColumns.map(
      (col: ColumnMeta) => ({ title: col.header, dataKey: col.field })
    );
    import('jspdf').then((jsPDF) => {
      import('jspdf-autotable').then(() => {
          const doc = new jsPDF.default('landscape');
          // I don't now why but ts can't find the function autoTable but it exists
          // The flag exists only to force it to compile otherwise it would raise errors
          // @ts-ignore
          doc.autoTable(exportColumns, response.data);
          doc.save(`${props.fileName}.pdf`);
      });
    });
  }

  const getHeader = (): ReactNode =>{
    return (
      <div className="flex flex-wrap align-items-center justify-content gap-2">
        <span>Select Columns:</span>
        <MultiSelect value={visibleColumns} options={allColumns} optionLabel="header" onChange={onColumnToggle} className="w-full sm:w-20rem" display="chip" />
        <Button type="button" icon="pi pi-filter-slash" label="Clear Filters" outlined onClick={clearFilter} />
        <Button type="button" icon="pi pi-file-pdf" label="PDF" onClick={exportPdf} data-pr-tooltip="PDF" />
        <Button type="button" icon="pi pi-file" label="CSV" onClick={() => exportCSV(false)} data-pr-tooltip="CSV" />
        <Button type="button" icon="pi pi-refresh" onClick={invalidateQuery} data-pr-tooltip="Refresh" />
      </div>);
  }

  const invalidateQuery = (): void => {
    queryClient.invalidateQueries({
      queryKey: props.queryKey
    });   // Triggers query refetch
    clearFilter();
  }

  const renderDataTable = (data: Array<any>): ReactNode => {
    return (<DataTable
      ref={dt}
      value={data}
      header={getHeader()}
      tableStyle={{ minWidth: '50rem' }}
      filters={filters}
      filterDisplay="menu"
      showGridlines
      stripedRows
      paginator
      reorderableColumns
      rows={10}
      rowsPerPageOptions={[5, 10, 25, 50]}
      sortMode="multiple"
      multiSortMeta={multiSortMeta}
      removableSort>
        {visibleColumns.map((col: ColumnMeta) => (
            <Column
              key={col.field}
              field={col.field}
              header={col.header}
              body={col.hasOwnProperty('body')? col.body: false}
              sortable
              filter
              filterPlaceholder="Search"/>
        ))}
    </DataTable>);
  }

  const refreshAllColumns = (): ColumnMeta[] => {
    if ((!props.expandField) || (response.data.length < 1)){
      return [...props.columns];
    }
    let columns: ColumnMeta[] = [...props.columns];
    const expandFieldKeys = Object.keys(response.data[0][props.expandField]);
    const expandField: ColumnMeta[] = expandFieldKeys.map((fieldName: string) =>{
      return {field: `${props.expandField}.${fieldName}`, header: snakeToSpaced(fieldName)};
    })
    return columns.concat(expandField);
  }

  const dt: any = useRef();
  const queryClient = useQueryClient();
  let allColumns = [...props.columns];

  const [filters, setFilters] = useState<DataTableFilterMeta>(getClearFilters());
  const [visibleColumns, setVisibleColumns] = useState<ColumnMeta[]>(props.columns);

  const multiSortMeta: DataTableSortMeta[] = [
    {
      field: 'updated_at',
      order: -1
    },
    {
      field: props.dataKey,
      order: 1
    },
  ]

  // Any exists only to deal with ts errors
  const {isError, isPending, data: response, error}: any = useQuery({
    queryKey: props.queryKey,
  });

  if (isError){
    toast.error('An error has occured');
    console.log(error);
    return renderDataTable([]);
  } else if (isPending){
    return renderDataTable([]);
  } else{
    allColumns = refreshAllColumns();
    return renderDataTable(response.data);
  }
}
