import { ReactNode } from "react";
import DataInfo from "../data_info/DataInfo";

interface EquipInfoProps {
  equipData: any    // Any is there just to give a typing
};

export default function EquipInfo(props: EquipInfoProps) {

  const getDetectorLocation = (data: any): string => {
    let locationText = data.location_name;
    if (data.location_description) {
      locationText += ` - ${data.location_description}`;
    }
    return locationText;
  }

  const fieldConfig = [
    { name: 'ID', field: 'id' },
    { name: 'Type', field: 'type_name' },
    { name: "Location", field: 'location_name', template: getDetectorLocation },
    { name: 'Status', field: 'status' },
    { name: 'Owner', field: 'owner_mne' },
    { name: 'Serial', field: 'serial' },
    { name: 'Comments', field: 'comments' },
  ];

  const expandField = ['type_params'];

  return (
    <DataInfo
      data={props.equipData}
      fields={fieldConfig}
      expandFields={expandField}/>
  );
}
