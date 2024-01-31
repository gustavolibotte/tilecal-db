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
import { makeMutationFunction, ENDPOINT } from '../../utils/DbClient';
import { toast } from 'react-toastify';
import { DEFAULT_ERROR_MSG } from '../../utils/constants';
import { AxiosError } from 'axios';


export default function FileView() {

  const { fileId } = useParams();
  const [activeIndex, setActiveIndex] = useState(0);
  const [visibleEditForm, setVisibleEditForm] = useState(false);
  const queryKey = ['file', 'get', {id: fileId, withDeleted: true}];
  const equipTableQuery = ['equipment', 'get', { relatedFileId: fileId }];

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
      ENDPOINT['file']
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
      toast.error(DEFAULT_ERROR_MSG);
    }
  });

  const restoreMutation = useMutation({
    mutationFn: makeMutationFunction(
      'patch',
      ENDPOINT['file']
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
    if (fileData.deleted_at){
      buttons = (
        <Button type="button" label="Restore" severity='success'
          onClick={() => {restoreMutation.mutate({id: fileId})}}/>
      );
    } else{
      buttons = (
        <>
        <Button type="button" label="Delete" severity='danger'
          onClick={() => {deleteMutation.mutate({id: fileId})}}/>
        <Button type="button" label="Edit" icon='pi pi-pencil'
          onClick={() => setVisibleEditForm(true)}/>
        <Dialog header="Edit equipment" visible={visibleEditForm} style={{ width: '50vw' }}
          onHide={() => setVisibleEditForm(false)}>
          <FileForm
            fileId={fileId}
            onSuccess={() => {
              queryClient.invalidateQueries({
                queryKey: queryKey
              });
            }}/>
        </Dialog>
        </>
      );
    }
    
    component = (
      <>
      <h1>File: {fileData.name}</h1>
      <TabPage activeIndex={activeIndex} onTabChange={(e) => setActiveIndex(e.index)}>
        <TabPanel header="Info">
          <div className="m-0">
            <div className="grid">
              <div className="col-6">
                <h2>File Info</h2>
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
