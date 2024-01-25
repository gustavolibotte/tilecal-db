import { useState } from 'react';
import { useParams } from 'react-router-dom';
import TabPage from '../../components/tab_page/TabPage';
import { TabPanel } from 'primereact/tabview';
import EquipInfo from '../../components/equip_info/EquipInfo';
import { useQuery } from '@tanstack/react-query';
import FileTable from '../../components/file_table/FileTable';
import EquipTable from '../../components/equip_table/EquipTable';
import './equip_view.css';
import NotFound from '../../components/not_found/NotFound';


export default function EquipView() {
  const { equipId } = useParams();
  const [activeIndex, setActiveIndex] = useState(0);
  console.log(equipId);

  const {isPending, isError, data: response}: any = useQuery({
    queryKey: ['equipment', 'get', {id: equipId}]
  });

  if (isPending){
    return <h1>Loading...</h1>;
  } else if(isError){
    console.log(response);
    return <h1>An Error has occured</h1>;
  }
  if (response.data.length < 1){
    return <NotFound/>;
  }
  const typeName = response.data[0].type_name;

  return (
    <>
    <h1>{typeName}: {equipId}</h1>
    <TabPage activeIndex={activeIndex} onTabChange={(e) => setActiveIndex(e.index)}>
      <TabPanel header="Info">
        <p className="m-0">
          <EquipInfo equipData={response.data[0]}/>
          <hr></hr>
          <h2>Related Equipment</h2>
          <EquipTable queryKey={['equipment', 'get', {relatedEquipmentId: equipId}]}/>
          <hr></hr>
          <h2>Related Files</h2>
          <FileTable queryKey={['file', 'get', {relatedEquipmentId: equipId}]}/>
        </p>
      </TabPanel>
      <TabPanel header="History">
        <p className="m-0">
          HISTORYYYYYYYYYYYYY
        </p>
      </TabPanel>
    </TabPage>
    </>
  )
}
