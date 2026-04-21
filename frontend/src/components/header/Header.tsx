import { Menubar } from 'primereact/menubar';
import { getEquipRegisterRoute, getSpecEquipRoute, ROUTES } from '../../utils/routes';
import { useUser } from '../../utils/user';
import { useQuery } from '@tanstack/react-query';
import './header.css'
import { useNavigate } from 'react-router-dom';
import GlobalSearch from '../global_search/GlobalSearch';
import logoImg from '../../assets/tilecal_scheme.png'; 

const GITLAB_ISSUES_URL = 'https://gitlab.cern.ch/tilecal-p2-db/tilecal-db/-/issues';
const DOCS_URL = 'https://tilecal-p2-db.docs.cern.ch/';

interface EquipType {
  name: string;
}

export default function Header() {
  const equipTypeQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', {}]
  });

  const navigate = useNavigate();
  const user: any = useUser();

  let equipTypes: EquipType[];
  if (equipTypeQuery.isError) {
    equipTypes = [];
  } else if (equipTypeQuery.isSuccess) {
    equipTypes = equipTypeQuery.data.data;
  } else {
    equipTypes = [];
  }

  const equipItems: any = [{
    label: 'Index',
    icon: 'pi pi-list',
    command: () => navigate('equipment')
  }];
  
  if (equipTypes.length > 0) {
    equipItems.push({ separator: true });
    equipTypes.forEach((et: EquipType) => {
      equipItems.push({
        label: et.name,
        command: () => navigate(getSpecEquipRoute(et.name))
      });
    });
  }

  const registerItems = [];
  if (user?.canRegister()) {
    registerItems.push({
      label: 'Batch Register',
      icon: 'pi pi-table',
      command: () => navigate('equipment/batch-register')
    });
    
    if (equipTypes.length > 0) {
      registerItems.push({ separator: true });
      equipTypes.forEach((et: EquipType) => {
        registerItems.push({
          label: et.name,
          command: () => navigate(getEquipRegisterRoute(et.name))
        });
      });
    }
  }

  const fileItems = [{
    label: 'Index',
    icon: 'pi pi-file',
    command: () => navigate('file')
  }];
  
  if (user?.canUpload()) {
    fileItems.push({
      label: 'Upload',
      icon: 'pi pi-upload',
      command: () => navigate('file/upload')
    });
  }

  const helpItems = [
    {
      label: 'Documentation',
      icon: 'pi pi-book',
      command: () => window.open(DOCS_URL, '_blank')
    },
    {
      label: 'Report Issue',
      icon: 'pi pi-github', 
      command: () => window.open(GITLAB_ISSUES_URL, '_blank')
    }
  ];

  const items = [
    { label: 'Equipment', icon: 'pi pi-server', items: equipItems },
    { label: 'Register', icon: 'pi pi-plus-circle', items: registerItems, visible: registerItems.length > 0 },
    { label: 'Files', icon: 'pi pi-folder-open', items: fileItems },
    { label: 'Assemble', icon: 'pi pi-wrench', command: () => navigate('equipment/assemble') },
    { label: 'Statistics', icon: 'pi pi-chart-bar', command: () => navigate(ROUTES.STATISTICS) },
    { label: 'QC Consistency', icon: 'pi pi-shield', command: () => navigate(ROUTES.QC_PMT_CONSISTENCY) },
    { label: 'Help', icon: 'pi pi-question-circle', items: helpItems }
  ];

  const start = (
    <div className="logo-section cursor-pointer" onClick={() => navigate('/')}>
      <img src={logoImg} alt="Tilecal Logo" className="header-logo" />
      <div className="logo-text">
        <span>Tilecal</span>
        <span>Electronics</span>
        <span>Database</span>
      </div>
    </div>
  );

  let end;
  if (user) {
    end = (
        <div className="header-end-container flex align-items-center gap-4">
            <div className="search-wrapper">
                <GlobalSearch />
            </div>
            <div className="flex flex-column align-items-end text-right">
                <span className="font-bold text-700">{user.username}</span>
                <span className="text-xs text-500 capitalize">{user.highestRole}</span>
            </div>
        </div>
    );
  } else {
    end = <span className="p-menuitem-text text-s">Loading</span>;
  }

  return (
    <div className="w-full sticky top-0" style={{ zIndex: 1000 }}>
      <Menubar 
        model={items} 
        start={start} 
        end={end}
        className='surface-0 shadow-2 border-none border-noround w-full app-header-menubar' 
      />
    </div>
  )
}