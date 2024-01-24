import { Component } from 'react';
import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import './equip_index.css';
import { EQUIPMENT_ENDPOINT } from '../../utils/DbClient';

export default class EquipIndex extends Component {

  render(){
    const getButton = (rowData: any) => {
      return <Button label={rowData.id}/>;
    }

    const columns: ColumnMeta[] = [
      {field: 'id', header: 'ID', body: getButton},
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
        expandField=''
        columns={columns}
        dataKey='id'
        dataKeyHeader='ID'
        fileName='equip_index'
        fetchUrl={EQUIPMENT_ENDPOINT}/>
      </>
    );
  }
  
}
