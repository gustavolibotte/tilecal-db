import { Component, ReactNode, createRef } from 'react'
import { DataTable, DataTableFilterMeta } from 'primereact/datatable';
import { MultiSelect, MultiSelectChangeEvent } from 'primereact/multiselect';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';
import { FilterMatchMode } from 'primereact/api';
import { DataTableSortMeta } from 'primereact/datatable';
import { ColumnBodyOptions } from 'primereact/column';
import { toast } from 'react-toastify';
import axios from 'axios';
import { snakeToSpaced } from '../../utils/string';
import './base_table.css';

interface TableProps {
    columns: ColumnMeta[];
    fetchUrl: string;
    dataKey: string;
    dataKeyHeader: string;
    fileName: string;
    expandField: string
};

interface TableState {
  data: Array<any>;
  filters: DataTableFilterMeta;
  visibleColumns: ColumnMeta[];
};

export interface ColumnMeta {
  field: string;
  header: string;
  body?: ReactNode | ((data: any, options: ColumnBodyOptions) => React.ReactNode)
};

export class BaseTable extends Component<TableProps, TableState> {

  dt: any;
  allColumns: ColumnMeta[];
  
  constructor(props: TableProps){
    super(props);
    this.allColumns = [...this.props.columns];
    this.state = {
      data: [],
      filters: this.getClearFilters(),
      visibleColumns: this.allColumns // Assigned by value
    }
    this.exportCSV = this.exportCSV.bind(this);
    this.clearFilter = this.clearFilter.bind(this);
    this.onColumnToggle = this.onColumnToggle.bind(this);
    this.exportPdf = this.exportPdf.bind(this);
    this.refresh = this.refresh.bind(this);
    this.dt = createRef();
  }

  refreshAllColumns(){
    if ((!this.props.expandField) || (this.state.data.length < 1)){
      return this.props.columns;
    }
    let allColumns: ColumnMeta[] = [...this.props.columns];
    const expandFieldKeys = Object.keys(this.state.data[0][this.props.expandField])
    console.log(this.state.data[0]);
    const expandField: ColumnMeta[] = expandFieldKeys.map((fieldName: string) =>{
      return {field: `${this.props.expandField}.${fieldName}`, header: snakeToSpaced(fieldName)};
    })
    this.allColumns = allColumns.concat(expandField);
    console.log(this.allColumns);
  }

  getClearFilters(): DataTableFilterMeta{
    let filters: DataTableFilterMeta = {};
    this.allColumns.forEach(
        colMeta => {
            filters[colMeta.field] = {value: null, matchMode: FilterMatchMode.STARTS_WITH}
        }
    );
    return filters;
  }

  async fetchData(){
    await axios.get(this.props.fetchUrl)
      .then((response)=>{
        this.setState({ data: response.data.data});
        toast.success('Loaded data');
      }, (error) =>{
        toast.error(error.data.message.error);
      });
  }

  getHeader(): ReactNode{
    return (
    <div className="flex flex-wrap align-items-center justify-content gap-2">
      <span>Select Columns:</span>
      <MultiSelect value={this.state.visibleColumns} options={this.allColumns} optionLabel="header" onChange={this.onColumnToggle} className="w-full sm:w-20rem" display="chip" />
      <Button type="button" icon="pi pi-filter-slash" label="Clear Filters" outlined onClick={this.clearFilter} />
      <Button type="button" icon="pi pi-file-pdf" label="PDF" onClick={this.exportPdf} data-pr-tooltip="PDF" />
      <Button type="button" icon="pi pi-file" label="CSV" onClick={() => this.exportCSV(false)} data-pr-tooltip="CSV" />
      <Button type="button" icon="pi pi-refresh" onClick={this.refresh} data-pr-tooltip="Refresh" />
    </div>);
  }

  render(): ReactNode {

    const multiSortMeta: DataTableSortMeta[] = [
      {
        field: 'updated_at',
        order: -1
      },
      {
        field: this.props.dataKey,
        order: 1
      },
    ]
    this.refreshAllColumns();

    return (
      <>
      <DataTable
        ref={this.dt}
        value={this.state.data}
        header={this.getHeader()}
        tableStyle={{ minWidth: '50rem' }}
        filters={this.state.filters}
        filterDisplay="menu"
        showGridlines
        stripedRows
        paginator
        rows={10}
        rowsPerPageOptions={[5, 10, 25, 50]}
        sortMode="multiple"
        multiSortMeta={multiSortMeta}
        removableSort>
          {this.state.visibleColumns.map((col: ColumnMeta) => (
              <Column
                key={col.field}
                field={col.field}
                header={col.header}
                body={col.hasOwnProperty('body')? col.body: false}
                sortable
                filter
                filterPlaceholder="Search"/>
          ))}
      </DataTable>
      </>
    );
  }

  componentDidMount(): void {
    this.fetchData();
  }

  exportCSV(selectionOnly: boolean): void {
    this.dt.current.exportCSV({
      selectionOnly: selectionOnly,
      filename: this.props.fileName
    });
  }

  exportPdf(): void{
    const exportColumns = this.state.visibleColumns.map(
      (col: ColumnMeta) => ({ title: col.header, dataKey: col.field })
    );
    import('jspdf').then((jsPDF) => {
      import('jspdf-autotable').then(() => {
          const doc = new jsPDF.default('landscape');
          // I don't now why but ts can't find the function autoTable but it exists
          // The flag exists only to force it to compile otherwise it would raise errors
          // @ts-ignore
          doc.autoTable(exportColumns, this.state.data);
          doc.save(`${this.props.fileName}.pdf`);
      });
  });
  }

  clearFilter(): void{
    this.setState({ filters: this.getClearFilters() });
  }

  onColumnToggle(event: MultiSelectChangeEvent): void{
    let selectedColumns = event.value;
    let orderedSelectedColumns = this.allColumns.filter((col: any) => selectedColumns.some((sCol: any) => sCol.field === col.field));
    this.setState({ visibleColumns: orderedSelectedColumns });
  }

  refresh(): void{
    this.fetchData();
    this.clearFilter();
  }
}
