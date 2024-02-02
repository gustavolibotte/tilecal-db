import { ReactNode, useState } from 'react';
import { useParams } from 'react-router-dom';
import TabPage from '../../components/tab_page/TabPage';
import { TabPanel } from 'primereact/tabview';
import EquipInfo from '../../components/equip_info/EquipInfo';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import EquipForm from '../../components/equip_form/EquipForm';
import PmtForm from '../../components/pmt_form/PmtForm';
import FileTable from '../../components/file_table/FileTable';
import NotFound from '../../components/not_found/NotFound';
import RelatedEquipTable from '../../components/related_equip_table/RelatedEquipTable';
import EquipHistTable from '../../components/equip_hist/EquipHistTable';
import './equip_view.css';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { toast } from 'react-toastify';
import { AxiosError } from 'axios';
import ButtonConfirmation from '../../components/button_confirmation/ButtonConfirmation';
import { useUser } from '../../utils/user';
import AssembleForm from '../../components/assemble_form/AssembleForm';


export default function EquipView() {

  const { equipId } = useParams();
  const [activeIndex, setActiveIndex] = useState(0);
  const [visibleEditForm, setVisibleEditForm] = useState(false);
  const [visibleAssembleForm, setvisibleAssembleForm] = useState(false);

  const user = useUser();

  const queryKey = ['equipment', 'get', {id: equipId, withDeleted: true}];
  const {isPending, isError, data: response}: any = useQuery({
    queryKey: queryKey
  });

  const queryClient = useQueryClient();

  const deleteMutation = useMutation({
    mutationFn: makeMutationFunction(
      'delete',
      getEndpoint('equipment')
    ),
    onSuccess: () => {
      toast.success('Equipment deleted with success.');
      queryClient.invalidateQueries({
        queryKey: queryKey
      })
    },
    onError: (error: AxiosError) => {
      toast.dismiss();
      console.log(error);
      toast.error('An error has occured.');
    }
  });

  const restoreMutation = useMutation({
    mutationFn: makeMutationFunction(
      'patch',
      getEndpoint('equipment')
    ),
    onSuccess: () => {
      toast.success('Equipment restored with success.');
      queryClient.invalidateQueries({
        queryKey: queryKey
      })
    },
    onError: (error: AxiosError) => {
      toast.dismiss();
      console.log(error);
      toast.error('An error has occured.');
    }
  });

  let component: ReactNode;
  if (isPending){
    component = <h1>Loading...</h1>;
  } else if(isError){
    console.log(response);
    // Have to put a better error management
    component = <h1>An Error has occured</h1>;
  } else{
    if (response.data.length < 1){
      return <NotFound/>;
    }
    const typeName = response.data[0].type_name;
    const onSuccess = () => {
      queryClient.invalidateQueries({
        queryKey: queryKey
      });
    }
    let editForm;
    if (typeName == 'PMT'){
      editForm = <PmtForm equipId={equipId} onSuccess={onSuccess}/>;
    } else{
      editForm = <EquipForm equipId={equipId} typeName={typeName} onSuccess={onSuccess}/>;
    }
    const equipData = response.data[0];
    let buttons;
    if (equipData.deleted_at && user.canRestore()){
      buttons = (
        <ButtonConfirmation
          actionLabel='Restore'
          actionSeverity='success'
          confirmSeverity='success'
          cancelSeverity='danger'
          onConfirmation={() => restoreMutation.mutate({id: equipId})}
          dialogHeader='Restore data?'/>
      );
    } else{
      buttons = (
        <>
        {user.canDelete() &&<ButtonConfirmation
          actionLabel='Delete'
          actionSeverity='danger'
          confirmSeverity='success'
          cancelSeverity='danger'
          onConfirmation={() => deleteMutation.mutate({id: equipId})}
          dialogHeader='Delete data?'/>}
        {user.canEdit() && <Button type="button" label="Edit" icon='pi pi-pencil'
          onClick={() => setVisibleEditForm(true)}/>}
        {user.canEdit() && <Dialog header="Edit equipment" visible={visibleEditForm}
          style={{ width: '50vw' }}
          onHide={() => setVisibleEditForm(false)}>
          {editForm}
        </Dialog>}
        </>
      );
    }

    let assembleButton: ReactNode = <></>;
    if(user.canEdit()){
      assembleButton = (
      <>
        <Button type='button' label='Assemble equipment'
          onClick={() => setvisibleAssembleForm(true)}/>
        <Dialog header='Assemble Equipment' visible={visibleAssembleForm}
          style={{ width: '50vw' }}
          onHide={() => setvisibleAssembleForm(false)}>
          <AssembleForm 
            parentId={equipId? equipId: ''}
            parentTypeName={equipData.type_name}/>
        </Dialog>
      </>
      );
    }
    
    component = (
      <>
      <h1>{typeName}: {equipId}</h1>
      <TabPage activeIndex={activeIndex} onTabChange={(e) => setActiveIndex(e.index)}>
        <TabPanel header="Info">
          <div className="m-0">
            <div className="grid">
              <div className="col-6">
                <h2>Equipment Info</h2>
              </div>
              <div className="col-6">
                <div className="flex flex-row-reverse flex-wrap gap-2">
                  {buttons}
                </div>
              </div>
            </div>
            <EquipInfo equipData={equipData}/>
            <hr></hr>
            <div className="grid">
              <div className="col-6">
                <h2>Related Equipment</h2>
              </div>
              <div className="col-6">
                <div className="flex flex-row-reverse flex-wrap gap-2">
                  {assembleButton}
                </div>
              </div>
            </div>
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
