import { ReactNode } from "react";
import { List, ListItem } from "../list/List";
import { snakeToSpaced } from "../../utils/string";

interface EquipInfoProps {
  equipData: any    // Any is there just to give a typing
};

export default function EquipInfo(props: EquipInfoProps) {

  const getItem = (name: string, value: any): ReactNode => {
    return (<ListItem key={name}><b>{name}</b>: {value}</ListItem>);
  }
  let locationText = props.equipData.location_name;
  if (props.equipData.location_description){
    locationText += ` - ${props.equipData.location_description}`;
  }

  let typeParamsItems: any = [];
  if (props.equipData.type_params){
    typeParamsItems = Object.keys(props.equipData.type_params).map((key: string) => {
      return getItem(snakeToSpaced(key), props.equipData.type_params[key]);
    });
  }

  return (
    <div className="col-12">
      <h2>Equipment Info</h2>
      <List>
        {getItem('ID', props.equipData.id)}
        {getItem('Type', props.equipData.type_name)}
        {getItem('Location', locationText)}
        {getItem('Status', props.equipData.status_name)}
        {getItem('Owner', props.equipData.owner_name)}
        {getItem('Serial', props.equipData.serial)}
        {typeParamsItems}
      </List>
    </div>
  );
}
