import { ReactNode, useRef, useState } from 'react';
import { useParams } from 'react-router-dom';
import TabPage from '../../components/tab_page/TabPage';
import { TabPanel } from 'primereact/tabview';
import EquipInfo from '../../components/equip_info/EquipInfo';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import EquipForm from '../../components/equip_form/EquipForm';
import PmtForm from '../../components/pmt_form/PmtForm';
import NotFound from '../../components/not_found/NotFound';
import RelatedEquipTable from '../../components/related_equip_table/RelatedEquipTable';
import EquipHistTable from '../../components/equip_hist/EquipHistTable';
import './equip_view.css';
import { getEndpoint, makeMutationFunction } from '../../utils/DbClient';
import { Id, toast } from 'react-toastify';
import { AxiosError } from 'axios';
import ButtonConfirmation from '../../components/button_confirmation/ButtonConfirmation';
import { useUser } from '../../utils/user';
import AssembleComponent from '../../components/assemble_component/AssembleComponent';
import { midTransactionToastConfig } from '../../utils/toast';
import RelateFileComponent from '../../components/relate_file_component/RelateFileComponent';
import RelatedFileTable from '../../components/related_file_table/RelatedFileTable';

export default function EquipView() {

  const { equipId } = useParams();
  const [activeIndex, setActiveIndex] = useState(0);
  const [visibleEditForm, setVisibleEditForm] = useState(false);

  const user = useUser();

  const queryKey = ['equipment', 'get', {id: equipId, withDeleted: true}];
  const {isPending, isError, data: response}: any = useQuery({
    queryKey: queryKey
  });

  const queryClient = useQueryClient();

  const deleteToastId = useRef<Id>();

  const deleteMutation = useMutation({
    mutationFn: makeMutationFunction(
      'delete',
      getEndpoint('equipment')
    ),
    onMutate: () => {
      deleteToastId.current = toast.info('Deleting equipment', midTransactionToastConfig);
    },
    onSuccess: () => {
      toast.dismiss(deleteToastId.current);
      toast.success('Equipment deleted with success.');
      queryClient.invalidateQueries({
        queryKey: queryKey
      })
    },
    onError: (error: AxiosError) => {
      toast.dismiss(deleteToastId.current);
      console.log(error);
      toast.error(__DEFAULT_ERROR_MSG__);
    }
  });

  const restoreToastId = useRef<Id>();

  const restoreMutation = useMutation({
    mutationFn: makeMutationFunction(
      'patch',
      getEndpoint('equipment')
    ),
    onMutate: () => {
      restoreToastId.current = toast.info('Restore equipment', midTransactionToastConfig);
    },
    onSuccess: () => {
      toast.dismiss(restoreToastId.current);
      toast.success('Equipment restored with success.');
      queryClient.invalidateQueries({
        queryKey: queryKey
      })
    },
    onError: (error: AxiosError) => {
      console.log(error);
      toast.dismiss(restoreToastId.current);
      toast.error(__DEFAULT_ERROR_MSG__);
    }
  });

  let component: ReactNode;
  if (isPending){
    component = <h1>Loading...</h1>;
  } else if(isError){
    console.log(response);
    component = <h1>An Error has occurred</h1>;
  } else{
    
    const responseData = response?.data || [];
    const equipArray = (!Array.isArray(responseData) && responseData.data !== undefined) 
      ? responseData.data 
      : responseData;

    if (equipArray.length < 1){
      return <NotFound/>;
    }
    const typeName = equipArray[0].type_name;
    const onSuccess = () => {
      queryClient.invalidateQueries({
        queryKey: queryKey
      });
      setVisibleEditForm(false);
    }
    let editForm;
    if (typeName == 'PMT'){
      editForm = <PmtForm equipId={equipId} onSuccess={onSuccess} isEdit/>;
    } else{
      editForm = <EquipForm equipId={equipId} typeName={typeName} onSuccess={onSuccess} isEdit/>;
    }
    const equipData = equipArray[0];
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
    let title;
    if (equipData.deleted_at){
      title = <h1 className='text-red-500'>DELETED {typeName}: {equipId}</h1>
    } else{
      title = <h1>{typeName}: {equipId}</h1>
    }
    component = (
      <>
      {title}
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
                  <AssembleComponent
                  disable={equipData.deleted_at}
                  equipId={equipId? equipId: ''}
                  typeName={equipData.type_name}/>
                </div>
              </div>
            </div>
            <RelatedEquipTable equipmentId={equipId}/>
            <hr></hr>
            <div className="grid">
              <div className="col-6">
                <h2>Related Files</h2>
              </div>
              <div className="col-6">
                <div className="flex flex-row-reverse flex-wrap gap-2">
                  <RelateFileComponent equipmentId={equipId? equipId: ''}/>
                </div>
              </div>
            </div>
            <RelatedFileTable equipmentId={equipId? equipId: ''}/>
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