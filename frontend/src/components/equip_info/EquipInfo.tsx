import { DataInfo} from "../data_info/DataInfo";
import { snakeToSpaced } from "../../utils/string";

interface EquipInfoProps {
  equipData: any    // Any is there just to give a typing
  hist?: boolean
};

export default function EquipInfo(props: EquipInfoProps) {

  const getDetectorLocation = (data: any): string => {
    let locationText = data.location_name;
    if (data.location_description) {
      locationText += ` - ${data.location_description}`;
    }
    return locationText;
  }

  let fieldConfig = [
    { name: 'ID', field: 'id' },
    { name: 'Type', field: 'type_name' },
    { name: "Location", field: 'location_name', template: getDetectorLocation },
    { name: 'Status', field: 'status_name' },
    { name: 'Owner', field: 'owner_name' },
    { name: 'Serial', field: 'serial' }
  ];

  fieldConfig = fieldConfig.concat(
    Object.keys(props.equipData.type_params).map((key: string): any => {
      return {
        name: snakeToSpaced(key),
        field: `type_params.${key}`,
        template: (data: any) => data.type_params[key]};
    })
  )

  fieldConfig = fieldConfig.concat([
    { name: 'Comments', field: 'comments' },
  ]);

  if (!props.hist){
    fieldConfig = fieldConfig.concat([
      { name: "Created At", field: 'created_at' },
      { name: "Updated At", field: 'updated_at' },
      { name: "Deleted At", field: 'deleted_at' }
    ]);
  }

  return (
    <DataInfo
      data={props.equipData}
      fields={fieldConfig}/>
  );
}
