import { useState } from 'react';
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

export default function Header() {
  const [isOpen, setIsOpen] = useState(false);

  const toggle = () => setIsOpen(!isOpen);
  return (
    <div>

      <Navbar color="dark" expand="lg">
        <NavbarBrand href="/" className="text-white">Tilecal Electronics Database</NavbarBrand>
        <NavbarToggler onClick={toggle} />
        <Collapse isOpen={isOpen} navbar>
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
                Files
              </DropdownToggle>
              <DropdownMenu end>
                <DropdownItem>Index</DropdownItem>
                <DropdownItem>Deleted</DropdownItem>
              </DropdownMenu>
            </UncontrolledDropdown>
          </Nav>
          <NavbarText className="text-white">Username</NavbarText>
        </Collapse>
      </Navbar>


    </div>
  )
}
