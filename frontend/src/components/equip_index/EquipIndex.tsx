import { Component } from 'react'
import EquipTable from '../datatables/equip_table/EquipTable';
import './equip_index.css';

export default class EquipIndex extends Component {

  render(){
    return (
      <>
        <h1>Equipment Index</h1>
        <EquipTable/>
      </>
    );
  }
  
}
