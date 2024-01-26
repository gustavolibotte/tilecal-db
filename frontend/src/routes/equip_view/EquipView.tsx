import { ReactNode, useState } from 'react';
import { useParams } from 'react-router-dom';
import TabPage from '../../components/tab_page/TabPage';
import { TabPanel } from 'primereact/tabview';
import EquipInfo from '../../components/equip_info/EquipInfo';
import { useQuery } from '@tanstack/react-query';
import FileTable from '../../components/file_table/FileTable';
import NotFound from '../../components/not_found/NotFound';
import RelatedEquipTable from '../../components/related_equip_table/RelatedEquipTable';
import EquipHistTable from '../../components/equip_hist/EquipHistTable';
import './equip_view.css';


export default function EquipView() {
  const { equipId } = useParams();
  const [activeIndex, setActiveIndex] = useState(0);

  const {isPending, isError, data: response}: any = useQuery({
    queryKey: ['equipment', 'get', {id: equipId}]
  });

  let component: ReactNode;
  if (isPending){
    component = <h1>Loading...</h1>;
  } else if(isError){
    console.log(response);
    component = <h1>An Error has occured</h1>;
  } else{
    if (response.data.length < 1){
      return <NotFound/>;
    }
    const typeName = response.data[0].type_name;
    component = (
      <>
      <h1>{typeName}: {equipId}</h1>
      <TabPage activeIndex={activeIndex} onTabChange={(e) => setActiveIndex(e.index)}>
        <TabPanel header="Info">
          <div className="m-0">
            <h2>Equipment Info</h2>
            <EquipInfo equipData={response.data[0]}/>
            <hr></hr>
            <h2>Related Equipment</h2>
            <RelatedEquipTable equipmentId={equipId}/>
            <hr></hr>
            <h2>Related Files</h2>
            <FileTable queryKey={['file', 'get', {relatedEquipmentId: equipId}]}/>
          </div>
        </TabPanel>
        <TabPanel header="History">
          <div className="m-0">
            <h1>Equipment History</h1>
            <EquipHistTable equipmentId={equipId? equipId: ''}/>
          </div>
        </TabPanel>
      </TabPage>
      </>
    );
  }
  return (
    <div className="col-12">
    {component}
    </div>
  )
}
