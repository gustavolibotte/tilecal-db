import { useQuery } from "@tanstack/react-query";
import AssembleForm from "../assemble_form/AssembleForm";
import RelatedEquipTable from "../related_equip_table/RelatedEquipTable";

interface EquipAssembleComponentsProps{
  parentId: string;
}

export default function EquipAssembleComponents({parentId}: EquipAssembleComponentsProps) {
  const {isError, isPending, data: response, error}: any = useQuery({
    queryKey: ['equipment', 'get', {id: parentId}]
  });
  if (isPending){
    return <h2>Loading...</h2>;
  } else if (isError){
    console.log('Error while querying equipment');
    console.log(error);
    return <h2>An error has occured</h2>;
  } else{
    if (response.data.length){
      const equipData = response.data[0];
      return (
        <>
          <h2>Assemble new equipment into {parentId}</h2>
          <AssembleForm parentId={parentId} parentTypeName={equipData.type_name}/>
          <hr/>
          <h2>Related to {parentId}</h2>
          <RelatedEquipTable equipmentId={parentId}/>
        </>);
    } else {
      return <h2>The equipment {parentId} was not found.</h2>;
    }
  }
}
