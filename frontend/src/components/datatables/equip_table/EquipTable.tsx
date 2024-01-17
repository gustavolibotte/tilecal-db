import { Component, ReactNode } from 'react'
import { DataTable, DataTableFilterMeta } from 'primereact/datatable';
import { Column } from 'primereact/column';
import './equip_table.css';
import axios from 'axios';
import { Button } from 'reactstrap';
import toastr from 'toastr';
import { FilterMatchMode } from 'primereact/api';

type EquipTableProps = {
  readonly headerText: string
};

type EquipTableState = {
  data: Array<any>,
  filters: DataTableFilterMeta,
  globalFilterValue: string
};

class EquipTable extends Component<EquipTableProps, EquipTableState> {

  constructor(props: any){
    super(props);
    this.state = {
      data: [],
      filters: {
        id: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
        type_label: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
        location_name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
        detector_location_name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
        status_name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
        owner_name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
        comments: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
        created_at: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
        updated_at: {value: null, matchMode: FilterMatchMode.STARTS_WITH}
      },
      globalFilterValue: ''
    }
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
      return <Button color='primary'>{rowData.id}</Button>;
    }

    return (
      <>
      <DataTable
        value={this.state.data}
        tableStyle={{ minWidth: '50rem' }}
        filters={this.state.filters}
        filterDisplay="row"
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
  
}

export default EquipTable
