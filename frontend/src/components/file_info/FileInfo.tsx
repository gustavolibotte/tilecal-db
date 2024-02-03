import { DataInfo } from "../data_info/DataInfo";

interface FileInfoProps {
  fileData: any
  hist?: boolean
}

export default function FileInfo(props: FileInfoProps) {

  let fieldConfig = [
    { name: 'Name', field: 'name' },
    { name: 'Description', field: 'description' }
  ];
  if (!props.hist){
    fieldConfig = fieldConfig.concat([
      { name: "Created At", field: 'created_at' },
      { name: "Updated At", field: 'updated_at' },
      { name: "Deleted At", field: 'deleted_at' }
    ]);
  }

  return (
    <DataInfo
      data={props.fileData}
      fields={fieldConfig} />
  );
}
