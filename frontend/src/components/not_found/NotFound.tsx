import { Component } from 'react'
import './not_found.css'
import { Col, Row, Container } from 'reactstrap'

export default class NotFound extends Component{
  render(){
    return (
      <Container fluid>
        <Row className="mt-4">
          <Col className="mt-4">
            <div className="jumbotron text-center align-items-center">
              <h1>This page was not found</h1>
              <p>This page was not found, please check if the link is valid. If it should be, please contact an admin.</p>
            </div>
          </Col>
        </Row>
      </Container>
    )
  }
}
