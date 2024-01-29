import { DataInfo } from "../data_info/DataInfo";

interface FileInfoProps {
  fileData: any
}

export default function FileInfo(props: FileInfoProps) {

  const fieldConfig = [
    { name: 'Name', field: 'name' },
    { name: 'Description', field: 'description' },
    { name: "Created At", field: 'created_at' },
    { name: "Updated At", field: 'updated_at' },
    { name: "Deleted At", field: 'deleted_at' }
  ];

  return (
    <DataInfo
      data={props.fileData}
      fields={fieldConfig} />
  );
}
