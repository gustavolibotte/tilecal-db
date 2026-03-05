import { ReactNode, useRef, useState } from 'react'
import { 
  DataTable,
  DataTableFilterMeta,
  DataTableExpandedRows,
  DataTableValueArray,
  DataTableBaseProps} from 'primereact/datatable';
import { MultiSelect, MultiSelectChangeEvent } from 'primereact/multiselect';
import { Column, ColumnProps } from 'primereact/column';
import { Button } from 'primereact/button';
import { FilterMatchMode } from 'primereact/api';
import { toast } from 'react-toastify';
import './base_table.css';
import { useQuery, useQueryClient } from '@tanstack/react-query';

export type ExpandRowType = DataTableExpandedRows | DataTableValueArray | undefined;
interface TableProps extends DataTableBaseProps<any> {
  columns: ColumnProps[];
  fileName: string;
  expandField: string;
  queryKey: Array<any>;
};

type OmittedColumnProps = Omit<Omit<ColumnProps, 'header'>, 'field'>;
export interface ColumnMeta extends OmittedColumnProps {
  header: string;
  field: string;
};

export function BaseTable(props: TableProps){

  const getClearFilters = (): DataTableFilterMeta => {
    let filters: DataTableFilterMeta = {};
    const filterableColumns = props.columns.filter(
      (colProps: ColumnProps) => Boolean(colProps.filter));
    filterableColumns.forEach(
        (colProps: any)  => {
            filters[colProps.field] = {value: null, matchMode: FilterMatchMode.STARTS_WITH}
        }
    );
    return filters;
  }

  const clearFilter = (): void => {
    setFilters(getClearFilters());
  }

  const onColumnToggle = (event: MultiSelectChangeEvent): void => {
    let selectedColumns = event.value;
    let orderedSelectedColumns = props.columns.filter((col: any) => selectedColumns.some((sCol: any) => sCol.field === col.field));
    setVisibleColumns(orderedSelectedColumns);
  }

  const exportCSV = (selectionOnly: boolean): void =>{
    dt.current.exportCSV({
      selectionOnly: selectionOnly,
      filename: props.fileName
    });
  }

  const getHeader = (): ReactNode =>{
    return (
      <div className="flex flex-wrap align-items-center justify-content gap-2">
        <span>Select Columns:</span>
        <MultiSelect value={visibleColumns} options={props.columns} optionLabel="header" onChange={onColumnToggle} className="w-full sm:w-20rem" display="chip" />
        <Button type="button" icon="pi pi-filter-slash" label="Clear Filters" outlined onClick={clearFilter} />
        {/* <Button type="button" icon="pi pi-file-pdf" label="PDF" onClick={exportPdf} data-pr-tooltip="PDF" /> */}
        <Button type="button" icon="pi pi-file" label="CSV" onClick={() => exportCSV(false)} data-pr-tooltip="CSV" />
        <Button type="button" icon="pi pi-refresh" onClick={invalidateQuery} data-pr-tooltip="Refresh" />
      </div>);
  }

  const invalidateQuery = (): void => {
    queryClient.invalidateQueries({
      queryKey: [props.queryKey[0], props.queryKey[1]]
    });   // Triggers query refetch
    clearFilter();
  }

  const dt: any = useRef();
  const queryClient = useQueryClient();

  const [filters, setFilters] = useState<DataTableFilterMeta>(getClearFilters());
  const [visibleColumns, setVisibleColumns] = useState<ColumnProps[]>(props.columns);

  const [lazyParams, setLazyParams] = useState({
    first: 0,
    rows: 10,
    page: 0,
  });

  const queryParams = { 
    ...props.queryKey[2], 
    limit: lazyParams.rows, 
    offset: lazyParams.first 
  };
  const paginatedQueryKey = [props.queryKey[0], props.queryKey[1], queryParams];

  const onPage = (event: any) => {
    setLazyParams({
      first: event.first,
      rows: event.rows,
      page: event.page
    });
  };

  // Any exists only to deal with ts errors
  const {isError, isPending, data: response, error}: any = useQuery({
    queryKey: paginatedQueryKey,
  });

  if (isError){
    toast.error('An error has occurred');
    console.log(error);
    return <p>An error has occurred when loading this table</p>;
  } else if (isPending){
    return <p>Loading...</p>;
  } else {
    // Essa lógica garante retrocompatibilidade com endpoints que não tem paginação
    const responseData = response?.data || [];
    const isPaginated = !Array.isArray(responseData) && responseData.data !== undefined;
    const tableData = isPaginated ? responseData.data : responseData;
    const totalRecords = isPaginated ? responseData.totalRecords : tableData.length;

    return (<DataTable
      ref={dt}
      value={tableData}
      header={getHeader()}
      lazy={true}
      totalRecords={totalRecords}
      first={lazyParams.first}
      rows={lazyParams.rows}
      onPage={onPage}
      tableStyle={{ minWidth: '50rem' }}
      filters={filters}
      filterDisplay="menu"
      showGridlines
      stripedRows
      paginator
      reorderableColumns
      rowsPerPageOptions={[5, 10, 25, 50, 100, 250, 500]} 
      sortMode={props.sortMode}
      multiSortMeta={props.multiSortMeta}
      removableSort
      expandedRows={props.expandedRows}
      onRowToggle={props.onRowToggle}
      rowExpansionTemplate={props.rowExpansionTemplate}>
        {visibleColumns.map((colProps: ColumnProps, i: number) => (
            <Column
              key={i}
              {...colProps}/>
        ))}
    </DataTable>);
  }
}