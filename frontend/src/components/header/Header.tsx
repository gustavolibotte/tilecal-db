import { Menubar } from 'primereact/menubar';
import { MenuItem } from 'primereact/menuitem';
import { useNavigate } from 'react-router-dom';
import { Button } from 'primereact/button';
import './header.css'

export default function Header(){

  const navigate = useNavigate();

  const navigateHome = () => {
    navigate('/');
  }

  const items: MenuItem[] = [
    {
      label: 'Equipment',
      items: [
        {
          label: 'Index',
          url: 'equipment'
        },
        {
          label: 'Deleted'
        }
      ]
    },
    {
      label: 'Register'
    },
    {
      label: 'Files',
      items: [
        {
          label: 'Index',
          url: 'files'
        },
        {
          label: 'Deleted'
        }
      ]
    }
  ]

  const start = <Button
    className="p-menuitem-text text-xl font-normal"
    label="Tilecal Electronics Database" onClick={navigateHome} link/>;
  const end = <span className="p-menuitem-text text-s">tilep2db</span>;
  
  return(
  <div className="card w-auto ">
      <Menubar model={items} start={start} end={end}
      className='shadow-2'/>
  </div>
  )
}
