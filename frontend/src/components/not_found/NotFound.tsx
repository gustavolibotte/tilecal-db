import { Component } from 'react'
import './not_found.css'
export default class NotFound extends Component{
  render(){
    return (
      <div className="jumbotron text-center align-items-center">
              <h1>This page was not found</h1>
              <p>This page was not found, please check if the link is valid. If it should be, please contact an admin.</p>
            </div>
    )
  }
}
