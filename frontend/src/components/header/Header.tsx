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

export default function Header() {
  const equipTypeQuery: any = useQuery({
    queryKey: ['equipment_type', 'get', {}]
  });

  const user: any = useUser();

  let equipTypes: EquipType[];
  if (equipTypeQuery.isError){
    console.log(equipTypeQuery.data);
    console.log(equipTypeQuery.error);
    equipTypes = [];
  } else if (equipTypeQuery.isSuccess){
    equipTypes = equipTypeQuery.data.data;
  } else {
    equipTypes = [];
  }

  const equipItems: any = [{ template: <MenuLink route='equipment' label='Index' /> }];
  if (user && user.canDelete()){
    equipItems.push({ template: <MenuLink route='deleted-equipment' label='Deleted' /> });
  }
  equipItems.push({ separator: true });
  equipTypes.forEach(
    (et: EquipType) => {
      // return {label: et.label, url: getSpecEquipRoute(et.label)};
      equipItems.push({ template: <MenuLink route={getSpecEquipRoute(et.name)} label={et.name} /> });
    }
  );

  let registerItems: any;
  if (user && user.canRegister()){
    registerItems = equipTypes.map(
      (et: EquipType) => {
        // return {label: et.label, url: getSpecEquipRoute(et.label)};
        return { template: <MenuLink route={getEquipRegisterRoute(et.name)} label={et.name} /> };
      }
    );
  } else{
    registerItems = [];
  }

  const fileItems = [{ template: <MenuLink route='file' label='Index' /> }];
  if (user && user.canUpload()){
    fileItems.push({ template: <MenuLink route='file/upload' label='Upload' /> });
  }
  if (user && user.canDelete()){
    fileItems.push({ template: <MenuLink route='deleted-files' label='Deleted' /> });
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
    }
  ]

  let end;
  if (user){
    end = <span className="p-menuitem-text text-s">{user.username}</span>;
  } else{
    end = <span className="p-menuitem-text text-s">Loading</span>;
  }

  return (
    <div className="card w-auto ">
      <Menubar model={items} start={<HomeButton/>} end={end}
        className='shadow-2' />
    </div>
  )
}

