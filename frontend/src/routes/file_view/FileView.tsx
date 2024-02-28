import { useState, ReactNode } from 'react';
import { useParams } from 'react-router-dom'
import { useQueryClient, useQuery, useMutation } from '@tanstack/react-query';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import TabPage from '../../components/tab_page/TabPage';
import { TabPanel } from 'primereact/tabview';
import NotFound from '../../components/not_found/NotFound';
import './file_view.css'
import FileInfo from '../../components/file_info/FileInfo';
import EquipTable from '../../components/equip_table/EquipTable';
import FileHistTable from '../../components/file_hist_table/FileHistTable';
import FileForm from '../../components/file_form/FileForm';
import { makeMutationFunction, getEndpoint } from '../../utils/DbClient';
import { toast } from 'react-toastify';
import { AxiosError } from 'axios';
import { useUser } from '../../utils/user';
import ButtonConfirmation from '../../components/button_confirmation/ButtonConfirmation';
import DownloadButton from '../../components/download_button/DownloadButton';

export default function FileView() {

  const { fileId } = useParams();
  const [activeIndex, setActiveIndex] = useState(0);
  const [visibleEditForm, setVisibleEditForm] = useState(false);
  const queryKey = ['file', 'get', {id: fileId, withDeleted: true}];
  const equipTableQuery = ['equipment', 'get', { relatedFileId: fileId }];
  const user = useUser();

  const {isPending, isError, data: response}: any = useQuery({
    queryKey: queryKey
  });

  const queryClient = useQueryClient();

  const invalidateQueries = () => {
    queryClient.invalidateQueries({
      queryKey: queryKey
    })
    queryClient.invalidateQueries({
      queryKey: equipTableQuery
    })
  }

  const deleteMutation = useMutation({
    mutationFn: makeMutationFunction(
      'delete',
      getEndpoint('file')
    ),
    onMutate: () => {
      toast.info('Deleting');
    },
    onSuccess: () => {
      toast.dismiss();
      toast.success('File deleted with success.');
      invalidateQueries();
    },
    onError: (error: AxiosError) => {
      toast.dismiss();
      console.log(error);
      toast.error(__DEFAULT_ERROR_MSG__);
    }
  });

  const restoreMutation = useMutation({
    mutationFn: makeMutationFunction(
      'patch',
      getEndpoint('file')
    ),
    onMutate: () => {
      toast.info('Restoring');
    },
    onSuccess: () => {
      toast.dismiss();
      toast.success('File restored with success.');
      invalidateQueries();
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
    const fileData = response.data[0];
    let buttons;
    if (fileData.deleted_at && user.canRestore()){
      buttons = (
        <ButtonConfirmation
          actionLabel='Restore'
          actionSeverity='success'
          confirmSeverity='success'
          cancelSeverity='danger'
          onConfirmation={() => restoreMutation.mutate({id: fileId})}
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
          onConfirmation={() => deleteMutation.mutate({id: fileId})}
          dialogHeader='Delete data?'/>}
        {user.canEdit() && <Button type="button" label="Edit" icon='pi pi-pencil'
          onClick={() => setVisibleEditForm(true)}/>}
        {user.canEdit() && <Dialog header="Edit equipment" visible={visibleEditForm} style={{ width: '50vw' }}
          onHide={() => setVisibleEditForm(false)}>
          <FileForm
            fileId={fileId}
            onSuccess={() => {
              queryClient.invalidateQueries({
                queryKey: queryKey
              });
            }}/>
        </Dialog>}
        </>
      );
    }

    component = (
      <>
      <h1>File:{fileData.name}</h1>
      <TabPage activeIndex={activeIndex} onTabChange={(e) => setActiveIndex(e.index)}>
        <TabPanel header="Info">
          <div className="m-0">
            <div className="grid">
              <div className="col-6">
                <div className="flex flex-wrap align-items-center justify-content gap-3">
                  <h2>File Info</h2>
                  <DownloadButton downloadUrl={getEndpoint('file_download')} params={{id: fileId}}/>
                </div>
              </div>
              <div className="col-6">
                <div className="flex flex-row-reverse flex-wrap gap-2">
                  {buttons}
                </div>
              </div>
            </div>
            <FileInfo fileData={fileData}/>
            <hr></hr>
            <h2>Related Equipment</h2>
            <EquipTable queryKey={equipTableQuery}/>
          </div>
        </TabPanel>
        <TabPanel header="History">
          <div className="m-0">
            <h1>File History</h1>
            <FileHistTable fileId={fileId? fileId: ''}/>
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
