import { createBrowserRouter, RouteObject, RouterProvider } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import Home from './routes/home/Home.tsx';
import NotFound from './components/not_found/NotFound.tsx';
import PageLayout from './components/page_layout/PageLayout.tsx';
import EquipIndex from './routes/equip_index/EquipIndex.tsx';
import FileIndex from './routes/file_index/FileIndex.tsx';
import SpecEquipIndex from './routes/spec_equip_index/SpecEquipIndex.tsx';
import EquipRegister from './routes/equip_register/EquipRegister.tsx';
import EquipView from './routes/equip_view/EquipView.tsx';
import { defaultQueryFunction } from './utils/DbClient.tsx';

import 'primeicons/primeicons.css';
import "primereact/resources/themes/bootstrap4-light-blue/theme.css";

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      queryFn: defaultQueryFunction
    }
  }
});

export default function App(){
  const children: RouteObject[] = [
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
      path: 'equipment/register/:typeName',
      element: <EquipRegister/>,
    },
    {
      path: 'equipment/:equipId',
      element: <EquipView/>,
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
  
  return (
    <QueryClientProvider client={queryClient}>
      <RouterProvider router={router} />
    </QueryClientProvider>
  );
}