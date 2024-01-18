import { Component } from 'react';
import { Outlet } from 'react-router-dom';
import Header from '../header/Header';
import './page_layout.css'


export default class PageLayout extends Component{
  render(){
  return(
    <div>
    <Header/>
    <Outlet/>
    </div>
  )
  }
}
