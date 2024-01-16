import React from 'react'
import ReactDOM from 'react-dom/client'
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
// import App from './App.tsx'
import './main.css'
import Home from './components/home/Home.tsx';
import NotFound from './components/not_found/NotFound.tsx';
import PageLayout from './components/page_layout/page_layout.tsx';

const children = [
  {
    path: "/",
    element: <Home />,
    errorElement: <NotFound />,
  },
  {
    path: "equipment",
    element: <Home/>,
  }
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
