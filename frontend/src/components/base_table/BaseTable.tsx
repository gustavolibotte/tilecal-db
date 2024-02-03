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

  const exportPdf = (): void => {
    const exportColumns = visibleColumns.map(
      (col: ColumnProps) => ({ title: col.header, dataKey: col.field })
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
        <MultiSelect value={visibleColumns} options={props.columns} optionLabel="header" onChange={onColumnToggle} className="w-full sm:w-20rem" display="chip" />
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

  const dt: any = useRef();
  const queryClient = useQueryClient();

  const [filters, setFilters] = useState<DataTableFilterMeta>(getClearFilters());
  const [visibleColumns, setVisibleColumns] = useState<ColumnProps[]>(props.columns);

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
    console.log(response.data);
    return renderDataTable(response.data);
  }
}
