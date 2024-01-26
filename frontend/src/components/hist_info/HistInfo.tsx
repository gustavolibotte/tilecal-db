import EquipInfo from '../equip_info/EquipInfo';
import EquipRelInfo from '../equip_rel_info/EquipRelInfo';
import FileInfo from '../file_info/FileInfo';
import './hist_info.css';

interface HistData {
  hist_type: string
  [otherKeys: string | number | symbol]: any
}
interface HistInfoProps {
  histData: HistData
}

export default function HistInfo(props: HistInfoProps) {

  switch (props.histData.hist_type){
    case 'equip':
      return (
        <EquipInfo equipData={props.histData}/>
      );
    case 'file':
      return (
        <FileInfo/>
      );
    case 'parent_rel':
      return(
        <EquipRelInfo/>
      );
    default:
      return (
        <h5> Could not render this history log.</h5>
      );
  }
}
