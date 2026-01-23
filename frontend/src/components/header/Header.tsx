import { Menubar } from 'primereact/menubar';
import { getEquipRegisterRoute, getSpecEquipRoute } from '../../utils/routes';
import { useUser } from '../../utils/user';
import { useQuery } from '@tanstack/react-query';
import './header.css'
import HomeButton from '../home_button/HomeButton';
import { useNavigate } from 'react-router-dom';

// Define constants for external URLs
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
    console.log(equipTypeQuery.data);
    console.log(equipTypeQuery.error);
    equipTypes = [];
  } else if (equipTypeQuery.isSuccess) {
    equipTypes = equipTypeQuery.data.data;
  } else {
    equipTypes = [];
  }

  // --- Menu Construction ---

  // 1. Equipment Menu
  const equipItems: any = [{
    label: 'Index',
    icon: 'pi pi-list',
    command: () => {
      navigate('equipment')
    }
  }];
  
  if (equipTypes.length > 0) {
    equipItems.push({ separator: true });
    equipTypes.forEach((et: EquipType) => {
      equipItems.push({
        label: et.name,
        command: () => {
          navigate(getSpecEquipRoute(et.name))
        }
      });
    });
  }

  // 2. Register Menu (Conditional)
  const registerItems = [];
  if (user.canRegister()) {
    registerItems.push({
      label: 'Batch Register',
      icon: 'pi pi-table',
      command: () => {
        navigate('equipment/batch-register')
      }
    });
    
    if (equipTypes.length > 0) {
      registerItems.push({ separator: true });
      equipTypes.forEach((et: EquipType) => {
        registerItems.push({
          label: et.name,
          command: () => {
            navigate(getEquipRegisterRoute(et.name))
          }
        });
      });
    }
  }

  // 3. Files Menu
  const fileItems = [{
    label: 'Index',
    icon: 'pi pi-file',
    command: () => {
      navigate('file')
    }
  }];
  
  if (user.canUpload()) {
    fileItems.push({
      label: 'Upload',
      icon: 'pi pi-upload',
      command: () => {
        navigate('file/upload')
      }
    });
  }

  // 4. Help Menu (New Feature)
  const helpItems = [
    {
      label: 'Documentation',
      icon: 'pi pi-book',
      command: () => {
        window.open(DOCS_URL, '_blank');
      }
    },
    {
      label: 'Report Issue',
      icon: 'pi pi-github', // generic git icon, usually appropriate for GitLab links too
      command: () => {
        window.open(GITLAB_ISSUES_URL, '_blank');
      }
    }
  ];

  // Main Menu Structure
  const items = [
    {
      label: 'Equipment',
      items: equipItems
    },
    {
      label: 'Register',
      items: registerItems,
      visible: registerItems.length > 0 // Hide if empty
    },
    {
      label: 'Files',
      items: fileItems
    },
    {
      label: 'Assemble',
      command: () => {
        navigate('equipment/assemble')
      }
    },
    {
      label: 'Help',
      items: helpItems
    }
  ];

  // User Display Logic
  let end;
  if (user) {
    end = <span className="p-menuitem-text text-s">{user.username} - {user.highestRole}</span>;
  } else {
    end = <span className="p-menuitem-text text-s">Loading</span>;
  }

  return (
    <div className="card w-auto ">
      <Menubar model={items} start={<HomeButton />} end={end}
        className='shadow-2' />
    </div>
  )
}