import { Component } from 'react';
import { ColumnMeta, BaseTable } from '../datatables/base_table/BaseTable';
import './equip_index.css';

export default class EquipIndex extends Component {

  render(){
    const visibleColumns: ColumnMeta[] = [
      {field: 'type_label', header: 'Type'},
      {field: 'location_name', header: 'Location'},
      {field: 'detector_location_name', header: 'Detector Location'},
      {field: 'status_name', header: 'Status'},
      {field: 'owner_name', header: 'Owner'},
      {field: 'comments', header: 'Comments'},
      {field: 'created_at', header: 'Created at'},
      {field: 'updated_at', header: 'Updated at'}
    ]
    return (
      <>
        <h1>Equipment Index</h1>
        <BaseTable
        visibleColumns={visibleColumns}
        dataKey='id'
        dataKeyHeader='ID'
        fetchUrl='http://localhost:8080/api/equipment'/>
      </>
    );
  }
  
}
