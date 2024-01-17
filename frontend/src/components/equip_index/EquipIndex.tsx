import { Component } from 'react'
import { Col, Container, Row } from 'reactstrap';
import EquipTable from '../datatables/equip_table/EquipTable';
import './equip_index.css';

export default class EquipIndex extends Component {

  render(){
    return (
      <>
      <Container fluid>
      <Row className="mt-4">
        <Col className="mt-4">
          <h1>Equipment Index</h1>
          <EquipTable headerText="Equipment Index"/>
        </Col>
      </Row>
      </Container>
      </>
    );
  }
  
}
