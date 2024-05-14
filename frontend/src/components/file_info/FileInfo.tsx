import { DataInfo } from "../data_info/DataInfo";

interface FileInfoProps {
  fileData: any
  hist?: boolean
}

export default function FileInfo(props: FileInfoProps) {

  const pathTemplate = (data: any) => {
    return <a href={data.path} target="_blank" rel="noreferrer">{data.path}</a>;
  }

  let fieldConfig = [
    { name: 'Name', field: 'name' },
    { name: 'Path', field: 'path', template: pathTemplate},
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
