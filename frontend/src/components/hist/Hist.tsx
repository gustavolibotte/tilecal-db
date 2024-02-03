import { getEquipviewRoute, getFileViewRoute } from '../../utils/routes';
import { Link } from 'react-router-dom';
import { capitalize } from '../../utils/string';
import { ReactNode } from 'react';

export interface HistData {
  hist_type: string
  [otherKeys: string | number | symbol]: any
}

export function HistAction(props: HistData): ReactNode {
  const histType = props.hist_type;
  const action = props.action;
  if (histType == 'file_rel' && action == 'insert') {
    return (
      <p>
        Links file <Link to={getFileViewRoute(props.file_id)}> {props.file_name} </Link> to <Link to={getEquipviewRoute(props.equipment_id)}>{props.equipment_id}</Link>
      </p>
    );
  } else if (histType == 'file_rel' && action == 'delete') {
    return (
      <p>
        Unlinks file <Link to={getFileViewRoute(props.file_id)}>{props.file_name} </Link> to <Link to={getEquipviewRoute(props.equipment_id)}>{props.equipment_id}</Link>
      </p>
    );
  } else if (histType == 'parent_rel' && action == 'insert') {
    return (
      <p>
        Assembles <Link to={getEquipviewRoute(props.child_equip_id)}>{props.child_equip_id}</Link> into <Link to={getEquipviewRoute(props.parent_equip_id)}> {props.parent_equip_id} </Link>
      </p>
    );
  } else if (histType == 'parent_rel' && action == 'delete') {
    return (
      <p>
        Disassembles <Link to={getEquipviewRoute(props.child_equip_id)}>{props.child_equip_id}</Link> into <Link to={getEquipviewRoute(props.parent_equip_id)}> {props.parent_equip_id} </Link>
      </p>
    );
  } else {
    return capitalize(props.action);
  }
}
