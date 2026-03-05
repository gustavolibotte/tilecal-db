import { useQuery } from "@tanstack/react-query";
import AssembleForm from "../assemble_form/AssembleForm";
import RelatedEquipTable from "../related_equip_table/RelatedEquipTable";
import { extractSequentialNumber } from '../../utils/validation_rules';

interface EquipAssembleComponentsProps{
  parentId: string;
}

export default function EquipAssembleComponents({parentId}: EquipAssembleComponentsProps) {
  const cleanParentId = extractSequentialNumber(parentId);
  const {isError, isPending, data: response, error}: any = useQuery({
    queryKey: ['equipment', 'get', {id: cleanParentId}]
  });
  
  if (isPending){
    return <h2>Loading...</h2>;
  } else if (isError){
    console.log('Error while querying equipment');
    console.log(error);
    return <h2>An error has occured</h2>;
  } else {
    const responseData = response?.data || [];
    const equipArray = (!Array.isArray(responseData) && responseData.data !== undefined) 
      ? responseData.data 
      : responseData;

    if (equipArray.length > 0){
      const equipData = equipArray[0];
      return (
        <>
          <h2>Assemble new equipment into {equipData.type_name} "{parentId}"</h2>
          <AssembleForm parentId={cleanParentId} parentTypeName={equipData.type_name} barcodeReaderSafe/>
          <hr/>
          <h2>Related to {equipData.type_name} "{parentId}"</h2>
          <RelatedEquipTable equipmentId={cleanParentId}/>
        </>);
    } else {
      return <h2>The equipment {parentId} was not found.</h2>;
    }
  }
}