import { ReactNode } from 'react';
import { Outlet } from 'react-router-dom';
import Header from '../header/Header';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import './page_layout.css'

interface PageLayoutProps {
  useOutlet? : boolean
  children?: ReactNode[] | ReactNode
}

export default function PageLayout(props: PageLayoutProps){
  if (props.useOutlet){
    return(
      <div>
        <ToastContainer/>
        <Header/>
        <Outlet/>
      </div>
    );
  }
  return(
    <div>
      <ToastContainer/>
      <Header/>
      {props.children}
    </div>
  )
}
