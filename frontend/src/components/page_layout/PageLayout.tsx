import { Component } from 'react';
import { Outlet } from 'react-router-dom';
import Header from '../header/Header';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import './page_layout.css'


export default class PageLayout extends Component{
  render(){
  return(
    <div>
      <ToastContainer/>
      <Header equipTypeUrl='http://localhost:8080/api/equipment-type'/>
      <Outlet/>
    </div>
  )
  }
}
