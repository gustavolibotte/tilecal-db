import React from 'react'
import ReactDOM from 'react-dom/client'
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import Home from './components/home/Home.tsx';
import NotFound from './components/not_found/NotFound.tsx';
import PageLayout from './components/page_layout/PageLayout.tsx';
import EquipIndex from './components/equip_index/EquipIndex.tsx';
import FileIndex from './components/file_index/FileIndex.tsx';
import SpecEquipIndex from './components/spec_equip_index/SpecEquipIndex.tsx';

import './main.css'
import 'primeicons/primeicons.css';
import "primereact/resources/themes/bootstrap4-light-blue/theme.css";


const children = [
  {
    path: "/",
    element: <Home />,
  },
  {
    path: "equipment",
    element: <EquipIndex/>,
  },
  {
    path: "equipment/type/:typeName",
    element: <SpecEquipIndex/>,
  },
  {
    path: "files",
    element: <FileIndex/>,
  },
]
const router = createBrowserRouter([
  {
    path: "/",
    element: <PageLayout />,
    errorElement: <NotFound />,
    children: children,
  },
]);

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <RouterProvider router={router} />
  </React.StrictMode>
)
