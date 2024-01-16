import { Component } from 'react'
import { Col, Container, Row } from 'reactstrap'
import './home.css'

export default class Home extends Component {
  render(){
    return (
      <>
      <Container fluid>
        <Row className="mt-4">
          <Col className="mt-4">
            <div className="jumbotron text-center">
              <h1>Welcome to the Tilecal Electronics Database</h1>
              <p>Here we can see the current status of each Tilecal component. Use the navbar on top to naviagte through the pages or choose one of the options bellow.</p>
            </div>
          </Col>
        </Row>
        <Row className="mt-4">
          <Col className="mt-4">
            <div className="text-center">
              <h3>Equipments</h3>
            </div>
          </Col>
          <Col className="mt-4">
            <div className="text-center">
              <h3>Files</h3>
            </div>
          </Col>
        </Row>
      </Container>
      </>
    );
  }
  
}
