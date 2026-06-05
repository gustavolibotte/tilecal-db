import { createBrowserRouter, RouteObject, RouterProvider } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import Home from './routes/home/Home.tsx';
import PageLayout from './components/page_layout/PageLayout.tsx';
import EquipIndex from './routes/equip_index/EquipIndex.tsx';
import FileIndex from './routes/file_index/FileIndex.tsx';
import SpecEquipIndex from './routes/spec_equip_index/SpecEquipIndex.tsx';
import EquipRegister from './routes/equip_register/EquipRegister.tsx';
import EquipView from './routes/equip_view/EquipView.tsx';
import FileView from './routes/file_view/FileView.tsx';
import { defaultQueryFunction } from './utils/DbClient.tsx';
import SearchResults from './routes/search_results/SearchResults.tsx';

import 'primeicons/primeicons.css';
import "primereact/resources/themes/md-light-indigo/theme.css";
import FileUpload from './routes/file_upload/FileUpload.tsx';
import EquipBatchRegister from './routes/equip_batch_register/EquipBatchRegister.tsx';
import EquipAssemble from './routes/assemble/EquipAssemble.tsx';
import ErrorPage from './routes/error_page/ErrorPage.tsx';

import { StatisticsDashboard } from './routes/statistics/StatisticsDashboard.tsx';
import { ROUTES } from './utils/routes.tsx';

import PmtConsistency from './routes/pmt_consistency/PmtConsistency.tsx';
import TileCal3D from './components/detector_3d/TileCal3D.tsx';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      queryFn: defaultQueryFunction,
      staleTime: 1*60*1000  // 1 minute in ms
    }
  }
});

export default function App(){
  const children: RouteObject[] = [
    { path: "/", element: <Home /> },
    { path: "index.php", element: <Home /> },
    { path: "callback", element: <Home /> },
    { path: "equipment", element: <EquipIndex/> },
    { path: "equipment/type/:typeName", element: <SpecEquipIndex/> },
    { path: "equipment/batch-register", element: <EquipBatchRegister/> },
    { path: 'equipment/register/:typeName', element: <EquipRegister/> },
    { path: 'equipment/:equipId', element: <EquipView/> },
    { path: 'equipment/assemble', element: <EquipAssemble/> },
    { path: 'file/upload', element: <FileUpload/> },
    { path: 'file/:fileId', element: <FileView/> },
    { path: "file", element: <FileIndex/> },
    { path: ROUTES.STATISTICS, element: <StatisticsDashboard/> },
    { path: ROUTES.SEARCH, element: <SearchResults/> },
    { path: ROUTES.QC_PMT_CONSISTENCY, element: <PmtConsistency/> },
    { path: "test-3d", element: <TileCal3D/> }
  ];
  
  const router = createBrowserRouter([
    {
      path: "/",
      element: <PageLayout useOutlet/>,
      errorElement: <ErrorPage useLayout/>,
      children: children,
    },
  ]);
  
  return (
    <QueryClientProvider client={queryClient}>
      <RouterProvider router={router} />
    </QueryClientProvider>
  );
}