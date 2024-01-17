import React from 'react'
import ReactDOM from 'react-dom/client'
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import Home from './components/home/Home.tsx';
import NotFound from './components/not_found/NotFound.tsx';
import PageLayout from './components/page_layout/page_layout.tsx';
import EquipIndex from './components/equip_index/EquipIndex.tsx';
import FileIndex from './components/file_index/FileIndex.tsx';
import toastr from 'toastr';

import './main.css'
import "primereact/resources/themes/lara-light-cyan/theme.css";

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

toastr.options.closeButton = true;
toastr.options.progressBar = true;

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <RouterProvider router={router} />
  </React.StrictMode>
)
