import { Component } from "react";
import { Link } from 'react-router-dom';
import './menu_link.css';

interface MenuLinkProps {
  route: string;
  label: string
}

export default class MenuLink extends Component<MenuLinkProps>{
  render(){
    return (
    <span className="flex align-items-center p-menuitem-link">
      <Link style={{ textDecoration: 'none' }} to={this.props.route}>{this.props.label}</Link>
    </span>);
  }
}