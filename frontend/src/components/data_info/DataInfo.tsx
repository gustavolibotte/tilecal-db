import { ReactNode } from "react";
import { List, ListItem } from "../list/List";
import { snakeToSpaced } from "../../utils/string";

interface DataInfoProps {
  data: any;
  fields: DataInfoFieldMeta[];
  expandFields: Array<string>;
};

interface DataInfoFieldMeta {
  name: string;
  field: string;
  template?: (data: any) => ReactNode
}

export default function DataInfo(props: DataInfoProps) {

  const getItem = (key: string, name: string, value: any): ReactNode => {
    return (<ListItem key={key}><b>{name}</b>: {value}</ListItem>);
  }

  let propsData = props.fields.map((fieldMeta: DataInfoFieldMeta) => {
    if (fieldMeta.template){
      return getItem(fieldMeta.field, fieldMeta.name, fieldMeta.template(props.data));
    } else{
      return getItem(fieldMeta.field, fieldMeta.name, props.data[fieldMeta.field]);
    }
  });

  const expandedData = props.expandFields.map((field: string) => {
    const fieldData = props.data[field];
    const expanded = Object.keys(fieldData).map((subField: string) => {
      return getItem(subField, snakeToSpaced(subField), fieldData[subField]);
    });
    return expanded;
  });
  propsData = propsData.concat(...expandedData);

  return (
    <List>
      {propsData}
    </List>
  );
}
