import { Component, ReactNode, createRef } from 'react'
import { DataTable, DataTableFilterMeta } from 'primereact/datatable';
import { MultiSelect, MultiSelectChangeEvent } from 'primereact/multiselect';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';
import { FilterMatchMode } from 'primereact/api';
import toastr from 'toastr';
import axios from 'axios';
import './base_table.css';

interface TableProps {
    visibleColumns: ColumnMeta[],
    fetchUrl: string,
    dataKey: string,
    dataKeyHeader: string
};

interface TableState {
  data: Array<any>;
  filters: DataTableFilterMeta;
  visibleColumns: ColumnMeta[]
};

export interface ColumnMeta {
  field: string;
  header: string;
};

export class BaseTable extends Component<TableProps, TableState> {

  dt: any;
  
  constructor(props: TableProps){
    super(props);
    this.state = {
      data: [],
      filters: this.getClearFilters(),
      visibleColumns: this.props.visibleColumns
    }
    this.exportCSV = this.exportCSV.bind(this);
    this.clearFilter = this.clearFilter.bind(this);
    this.onColumnToggle = this.onColumnToggle.bind(this);
    this.dt = createRef();
  }

  getClearFilters(): DataTableFilterMeta{
    let filters: DataTableFilterMeta = {}
    this.props.visibleColumns.forEach(
        columnDescription => {
            filters[columnDescription.field] = {value: null, matchMode: FilterMatchMode.STARTS_WITH}
        }
    );
    return filters;
  }

  async fetchData(){
    toastr.info('Fetching data');
    await axios.get(this.props.fetchUrl)
      .then((response)=>{
        this.setState({ data: response.data.data});
        toastr.remove();
        toastr.success('Loaded Data')
      }, (error) =>{
        toastr.remove();
        toastr.error('Error', error.response.data.messages.error);
      });
  }

  getHeader(): ReactNode{
    return (<div className="flex align-items-center justify-content gap-2">
        <span>Select Columns:</span>
        <MultiSelect value={this.state.visibleColumns} options={this.props.visibleColumns} optionLabel="header" onChange={this.onColumnToggle} className="w-full sm:w-20rem" display="chip" />
        <Button type="button" icon="pi pi-filter-slash" label="Clear Filters" outlined onClick={this.clearFilter} />
        <Button type="button" icon="pi pi-file" label="CSV Export" onClick={() => this.exportCSV(false)} data-pr-tooltip="CSV" />
    </div>);
  }

  render(): ReactNode {

    const getButton = (rowData: any) => {
      return <Button label={rowData[this.props.dataKey]}/>;
    }

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
        removableSort
        dataKey={this.props.dataKey}>
          <Column field={this.props.dataKey} header={this.props.dataKeyHeader} body={getButton} sortable filter filterPlaceholder="Search"/>
          {this.state.visibleColumns.map((col) => (
              <Column key={col.field} field={col.field} header={col.header} filter filterPlaceholder="Search"/>
          ))}
      </DataTable>
      </>
    );
  }

  componentDidMount(): void {
    this.fetchData();
  }

  exportCSV(selectionOnly: boolean): void {
    this.dt.current.exportCSV({ selectionOnly });
  }

  clearFilter(): void{
    this.setState({ filters: this.getClearFilters() });
  }

  onColumnToggle(event: MultiSelectChangeEvent): void{
    let selectedColumns = event.value;
    let orderedSelectedColumns = this.props.visibleColumns.filter((col) => selectedColumns.some((sCol: any) => sCol.field === col.field));
    this.setState({ visibleColumns: orderedSelectedColumns });
  }
}
