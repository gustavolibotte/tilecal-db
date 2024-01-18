import { Component, ReactNode, createRef } from 'react'
import { DataTable, DataTableFilterMeta } from 'primereact/datatable';
import { Column } from 'primereact/column';
import './equip_table.css';
import axios from 'axios';
import { Button } from 'primereact/button';
import toastr from 'toastr';
import { FilterMatchMode } from 'primereact/api';

type EquipTableProps = {};

type EquipTableState = {
  data: Array<any>,
  filters: DataTableFilterMeta
};

class EquipTable extends Component<EquipTableProps, EquipTableState> {

  dt: any;
  
  constructor(props: any){
    super(props);
    this.state = {
      data: [],
      filters: this.getClarFilters(),
    }
    this.exportCSV = this.exportCSV.bind(this);
    this.clearFilter = this.clearFilter.bind(this);
    this.dt = createRef();
  }

  getClarFilters(): DataTableFilterMeta{
    const filters: DataTableFilterMeta = {
      id: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
      type_label: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
      location_name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
      detector_location_name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
      status_name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
      owner_name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
      comments: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
      created_at: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
      updated_at: {value: null, matchMode: FilterMatchMode.STARTS_WITH}
    };
    return filters;
  }

  async fetchData(){
    toastr.info('Fetching data');
    await axios.get('http://localhost:8080/api/equipment')
      .then((response)=>{
        this.setState({ data: response.data.data});
        toastr.remove();
        toastr.success('Loaded Data')
      }, (error) =>{
        toastr.remove();
        toastr.error('Error', error.response.data.messages.error);
      });
  }

  render(): ReactNode {

    const getButton = (rowData: any) => {
      return <Button label={rowData.id}/>;
    }

    const header: ReactNode = (     
      <div className="flex align-items-center justify-content gap-2">
        <Button type="button" icon="pi pi-filter-slash" label="Clear Filters" outlined onClick={this.clearFilter} />
        <Button type="button" icon="pi pi-file" label="CSV Export" onClick={() => this.exportCSV(false)} data-pr-tooltip="CSV" />
      </div>
    )

    return (
      <>
      <DataTable
        ref={this.dt}
        value={this.state.data}
        header={header}
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
        dataKey="id">
        <Column field='id' header='ID' body={getButton} sortable filter filterPlaceholder="Search"/>
        <Column field='type_label' header='Type' sortable filter filterPlaceholder="Search"/>
        <Column field='location_name' header='Location' sortable filter filterPlaceholder="Search"/>
        <Column field='detector_location_name' header='Detector Location' sortable filter filterPlaceholder="Search"/>
        <Column field='status_name' header='Status' sortable filter filterPlaceholder="Search"/>
        <Column field='owner_name' header='Owner' sortable filter filterPlaceholder="Search"/>
        <Column field='comments' header='Comments' sortable filter filterPlaceholder="Search"/>
        <Column field='created_at' header='Created At' sortable filter filterPlaceholder="Search"/>
        <Column field='updated_at' header='Updated At' sortable filter filterPlaceholder="Search"/>
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
    this.setState({ filters: this.getClarFilters() });
  }
  
}

export default EquipTable
