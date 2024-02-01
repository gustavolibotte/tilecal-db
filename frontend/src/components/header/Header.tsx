import { Menubar } from 'primereact/menubar';
import MenuLink from '../menu_link/MenuLink';
import { getEquipRegisterRoute, getSpecEquipRoute } from '../../utils/routes';
import { useUser } from '../../utils/user';
import { useQuery } from '@tanstack/react-query';
import './header.css'
import HomeButton from '../home_button/HomeButton';

interface EquipType {
  name: string;
}

function HeaderUsername(){

  const user: any = useUser();
  
  if (user){
    return <span className="p-menuitem-text text-s">{user.username}</span>;
  }
  return <span className="p-menuitem-text text-s">Loading</span>;
}

export default function Header() {
  const equipTypeQuery: any = useQuery({
    queryKey: ['equipment_types', 'get', {}]
  });

  let equipTypes;
  if (equipTypeQuery.isError){
    console.log(equipTypeQuery);
    equipTypes = [];
  } else if (equipTypeQuery.isSuccess){
    equipTypes = equipTypeQuery.data.response.data;
  } else {
    equipTypes = [];
  }

  let equipItems: any = equipTypes.map(
    (et: EquipType) => {
      // return {label: et.label, url: getSpecEquipRoute(et.label)};
      return { template: <MenuLink route={getSpecEquipRoute(et.name)} label={et.name} /> };
    }
  );
  let registerItems: any = equipTypes.map(
    (et: EquipType) => {
      // return {label: et.label, url: getSpecEquipRoute(et.label)};
      return { template: <MenuLink route={getEquipRegisterRoute(et.name)} label={et.name} /> };
    }
  );
  equipItems.unshift({ separator: true });
  equipItems.unshift({ template: <MenuLink route='deleted-equipment' label='Deleted' /> });
  equipItems.unshift({ template: <MenuLink route='equipment' label='Index' /> });
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
      items: [
        { template: <MenuLink route='file' label='Index' /> },
        { template: <MenuLink route='file/upload' label='Upload' /> },
        { template: <MenuLink route='deleted-files' label='Deleted' /> }
      ]
    }
  ]

  return (
    <div className="card w-auto ">
      <Menubar model={items} start={<HomeButton/>} end={<HeaderUsername/>}
        className='shadow-2' />
    </div>
  )
}

