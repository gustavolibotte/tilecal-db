import { ReactNode } from "react";
import { List, ListItem } from "../list/List";

export interface DataInfoProps {
  data: any;
  fields: DataInfoFieldMeta[];
};

interface DataInfoFieldMeta {
  name: string;
  field: string;
  template?: (data: any) => ReactNode
}

export function DataInfo(props: DataInfoProps) {

  const getItem = (key: string, name: string, value: any): ReactNode => {
    return (<ListItem key={key}><b>{name}</b>: {value}</ListItem>);
  }

  const propsData = props.fields.map((fieldMeta: DataInfoFieldMeta) => {
    if (fieldMeta.template){
      return getItem(fieldMeta.field, fieldMeta.name, fieldMeta.template(props.data));
    } else{
      return getItem(fieldMeta.field, fieldMeta.name, props.data[fieldMeta.field]);
    }
  });

  return (
    <List>
      {propsData}
    </List>
  );
}
