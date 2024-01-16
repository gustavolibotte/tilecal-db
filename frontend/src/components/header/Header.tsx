import { Component } from 'react';
import { Navbar,
  NavbarBrand,
  NavbarToggler,
  Collapse,
  Nav,
  UncontrolledDropdown,
  DropdownToggle,
  DropdownMenu,
  DropdownItem,
  NavbarText} from 'reactstrap'
import './header.css'

type HeaderState = {
  isOpen: boolean
};

export default class Header extends Component{
  state: HeaderState = {
    isOpen: false
  };

  toggleNavBar(){
    this.setState({isOpen: !this.state.isOpen});
  }

  render(){
    return(
      <div>
      <Navbar color="dark" expand="lg">
        <NavbarBrand href="/" className="text-white">Tilecal Electronics Database</NavbarBrand>
        <NavbarToggler onClick={this.toggleNavBar} />
        <Collapse isOpen={this.state.isOpen} navbar>
          <Nav className="ms-auto" navbar>
            <UncontrolledDropdown nav inNavbar>
              <DropdownToggle nav caret className="text-white">
                Equipment
              </DropdownToggle>
              <DropdownMenu end>
                <DropdownItem>Index</DropdownItem>
                <DropdownItem>Deleted</DropdownItem>
                <DropdownItem divider />
                <DropdownItem>Specific Equipment</DropdownItem>
              </DropdownMenu>
            </UncontrolledDropdown>
            <UncontrolledDropdown nav inNavbar>
              <DropdownToggle nav caret className="text-white">
                Register
              </DropdownToggle>
              <DropdownMenu end>
                <DropdownItem>Batch Register</DropdownItem>
              </DropdownMenu>
            </UncontrolledDropdown>
            <UncontrolledDropdown nav inNavbar>
              <DropdownToggle nav caret className="text-white">
                Files
              </DropdownToggle>
              <DropdownMenu end>
                <DropdownItem>Index</DropdownItem>
                <DropdownItem>Deleted</DropdownItem>
              </DropdownMenu>
            </UncontrolledDropdown>
          </Nav>
          <NavbarText className="text-white">tilep2db</NavbarText>
        </Collapse>
      </Navbar>
      </div>
    )
  }
}
