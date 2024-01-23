import { Menubar } from 'primereact/menubar';
import { Navigate} from 'react-router-dom';
import { Button } from 'primereact/button';
import { Component} from 'react';
import './header.css'
import MenuLink from '../menu_link/MenuLink';
import axios from 'axios';
import { toast } from 'react-toastify';
import { getEquipRegisterRoute, getSpecEquipRoute } from '../../utils/routes';

interface HeaderProps {
  equipTypeUrl: string;
};

interface EquipType {
  name: string;
  label: string;
}

interface HeaderState {
  toHome: boolean;
  equipTypes: EquipType[] 
}


export default class Header extends Component<HeaderProps, HeaderState>{

  constructor(props: any){
    super(props);
    this.state = {
      toHome: false,
      equipTypes: []
    }
    this.navigateHome = this.navigateHome.bind(this);
  }

  navigateHome(): void{
    this.setState({toHome: true})
  }

  componentDidMount(): void{
    this.fetchData();
  }

  componentDidUpdate(): void{
    if (this.state.toHome){
      this.setState({toHome: false});
    }
  }

  async fetchData(){
    await axios.get(this.props.equipTypeUrl)
      .then((response)=>{
        this.setState({ equipTypes: response.data.data});
      }, (error) =>{
        toast.error(error.data.message.error);
      });
  }

  render(){

    if (this.state.toHome){
      return <Navigate to='/'/>;
    }
    
    let equipItems: any = this.state.equipTypes.map(
      (et: EquipType) => {
        // return {label: et.label, url: getSpecEquipRoute(et.label)};
        return {template: <MenuLink route={getSpecEquipRoute(et.name)} label={et.name}/>};
      }
    );
    let registerItems: any = this.state.equipTypes.map(
      (et: EquipType) => {
        // return {label: et.label, url: getSpecEquipRoute(et.label)};
        return {template: <MenuLink route={getEquipRegisterRoute(et.name)} label={et.name}/>};
      }
    );
    equipItems.unshift({ separator: true});
    equipItems.unshift({ template: <MenuLink route='deleted-equipment' label='Deleted'/> });
    equipItems.unshift({ template: <MenuLink route='equipment' label='Index'/> });
    const items =  [
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
          { template: <MenuLink route='files' label='Index'/> },
          { label: <MenuLink route='deleted-files' label='Deleted'/> }
        ]
      }
    ]
    const start = <Button
      className="p-menuitem-text text-xl font-normal"
      label="Tilecal Electronics Database" onClick={this.navigateHome} link/>;
    const end = <span className="p-menuitem-text text-s">tilep2db</span>;
    
    return(
    <div className="card w-auto ">
        <Menubar model={items} start={start} end={end}
        className='shadow-2'/>
    </div>
    )
  }
}
