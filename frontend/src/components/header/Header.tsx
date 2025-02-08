import { Menubar } from 'primereact/menubar';
import { getEquipRegisterRoute, getSpecEquipRoute } from '../../utils/routes';
import { useUser } from '../../utils/user';
import { useQuery } from '@tanstack/react-query';
import './header.css'
import HomeButton from '../home_button/HomeButton';
import { useNavigate } from 'react-router-dom';

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

  const equipItems: any = [{
    label: 'Index',
    command: () => {
      navigate('equipment')
    }
  }];
  equipItems.push({ separator: true });
  equipTypes.forEach(
    (et: EquipType) => {
      // return {label: et.label, url: getSpecEquipRoute(et.label)};
      equipItems.push({
        label: et.name,
        command: () => {
          navigate(getSpecEquipRoute(et.name))
        }
      });
    }
  );

  const registerItems = [];
  if (user.canRegister()) {
    registerItems.push({
      label: 'Batch Register',
      command: () => {
        navigate('equipment/batch-register')
      }
    });
    registerItems.push({ separator: true });
    equipTypes.forEach(
      (et: EquipType) => {
        registerItems.push({
          label: et.name,
          command: () => {
            navigate(getEquipRegisterRoute(et.name))
          }
        });
      }
    );
  }

  const fileItems = [{
    label: 'Index',
    command: () => {
      navigate('file')
    }
  }];
  if (user.canUpload()) {
    fileItems.push({
      label: 'Upload',
      command: () => {
        navigate('file/upload')
      }
    });
  }

  const items = [
    {
      label: 'Equipment',
      items: equipItems
    },
    {
      label: 'Register',
      items: registerItems
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
    }
  ]

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

