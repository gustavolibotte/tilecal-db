// PMT positions integration v1
import PmtPositions from "../pmt_positions/PmtPositions";
import { DataInfo } from "../data_info/DataInfo";
import { snakeToSpaced, formatDateTime } from "../../utils/string";

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
    { name: 'Detector Location', field: 'detector_location_name'},
    { name: 'Status', field: 'status_name' },
    { name: 'Owner', field: 'owner_name' },
    { name: 'Serial', field: 'serial' }
  ];

  if (props.equipData.type_params != null){
    fieldConfig = fieldConfig.concat(
      Object.keys(props.equipData.type_params).filter(key => props.hist || props.equipData.type_name !== 'PMT' || !['phase1_position', 'box_position'].includes(key)).map((key: string): any => {
        return {
          name: snakeToSpaced(key? key: ''),
          field: `type_params.${key}`,
          template: (data: any) => data.type_params[key]};
      })
    );
  }

  fieldConfig = fieldConfig.concat([
    { name: 'Comments', field: 'comments' },
  ]);

  if (!props.hist){
    fieldConfig = fieldConfig.concat([
      { name: "Created At", field: 'created_at', template: (data: any) => formatDateTime(data.created_at) },
      { name: "Updated At", field: 'updated_at', template: (data: any) => formatDateTime(data.updated_at) },
      { name: "Deleted At", field: 'deleted_at', template: (data: any) => formatDateTime(data.deleted_at) }
    ]);
  }

  return (
    <>
    <DataInfo
      data={props.equipData}
      fields={fieldConfig}/>
    {!props.hist && !props.equipData.deleted_at && ['PMT', 'PMT Block', 'PMT Block (Standard)', 'PMT Block (Long Cable)'].includes(props.equipData.type_name) &&
      <PmtPositions key={props.equipData.id} equipmentId={props.equipData.id} />}
    </>
  );
}