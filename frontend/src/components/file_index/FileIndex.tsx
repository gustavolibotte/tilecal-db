import { Component } from 'react'
import { Col, Container, Row } from 'reactstrap'
import './file_index.css'

export default class FileIndex extends Component {
  render(){
  return (
    <>
    <Container fluid>
    <Row className="mt-4">
      <Col className="mt-4">
        Content
      </Col>
    </Row>
    </Container>
    </>
  );
  }
  
}
