<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use App\Library\Forms\FileForm;
use App\Models\HistModel;

class Files extends BaseController
{
    public function index()
    {
        $indexData['pageTitle'] = 'File Index';
        $indexData['pageHeader'] = 'File Index';
        return default_view('files/index', $indexData);
    }

    public function deletedIndex(): string
    {
        $indexData['pageTitle'] = 'Deleted File Index';
        $indexData['pageHeader'] = 'Deleted File Index';
        $indexData['headerClass'] = 'text-danger';
        return default_view('files/deleted_index', $indexData);
    }

    public function view($fileId){
        helper('form');
        $fileModel = new FileModel();
        $file = $fileModel->withDeleted()->getFile($fileId);
        if (is_null($file)){
            return redirect('notfound');
        } elseif ($file->isDeleted() && !$this->user->isAuthorized('delete')){
            return redirect ('deleted-entry');
        }

        $viewData['entity'] = $file;
        $viewData['pageHeader'] = sprintf('File %s', $file->name);
        $viewData['infoHeader'] = 'File Info';
        $viewData['histories'] = (new HistModel())->getFileHist($file->id);

        if ($file->isDeleted()){
            $viewData['pageHeader'] = 'Deleted ' . $viewData['pageHeader'];
            $viewData['headerClass'] = 'text-danger';
        }
        $viewData['pageTitle'] = $viewData['pageHeader'];

        $viewData['editable'] = false;
        if ($viewData['editable']){
            $viewData['editForm'] = new FileForm();
            $viewData['editForm']->asEdit();
            $viewData['editForm']->setFieldValues($file->toArray());
            $viewData['editUrl'] = base_url(sprintf('equipments/%s/edit/%s', $file->type_name, $file->id));
        }
        $viewData['deletable'] = $this->user->isAuthorized('delete') && (!$file->isDeleted());
        if ($viewData['deletable']){
            $viewData['deleteUrl'] = base_url(sprintf('files/delete/%d', $file->id));
        }

        $viewData['restorable'] = $this->user->isAuthorized('delete') && $file->isDeleted();
        if ($viewData['restorable']){
            $viewData['restoreUrl'] = base_url(sprintf('files/restore/%s', $file->id));
        }

        return default_view('files/view', $viewData);
    }

    public function download($fileId){
        $fileModel = new FileModel();
        $file = $fileModel->find($fileId);
        header('Content-Description: File Transfer');
        header(sprintf('Content-Type: %s', $file->mime));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $file->name));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($file->filedata));
        echo $file->filedata;
    }

    public function upload(){
        helper(['form', 'string', 'url', 'message']);
        $session = \Config\Services::session();
        $messenger = service('messenger');

        if ($this->request->is('post')){
            $form = new FileForm($this->request);
            if ($form->validate()){
                $file = $form->getEntity();
                $fileModel = new FileModel();
                $fileModel->insert($file);
                $messenger->addMessage('File uploaded with success', 'success');
                return redirect('files/upload');
            } else {
                foreach ($form->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                };
            }
        } else{
            $form = new FileForm();
        }
        
        $title = 'Upload File';
        $registerData['pageTitle'] = $title;
        $registerData['pageHeader'] = $title;
        $registerData['forms'] = ['File Info'=> $form];
        $registerData['formUrl'] = uri_string();
        $registerData['cancelUrl'] = base_url('files');
        $messenger->dumpMessages();

        return default_view('files/upload', $registerData);
    }

    public function delete($fileId)
    {
        $fileModel = new FileModel();
        $fileModel->delete($fileId);
        $messenger = service('messenger');
        $messenger->addDeleteSuccess();
        if ($this->user->isAuthorized('delete')){
            return redirect()->to(base_url(sprintf('files/view/%s', $fileId)));
        } else {
            return redirect('files');
        }
        $messenger->dumpMessages();
    }

    public function restore($fileId)
    {
        $fileModel = new FileModel();
        $fileModel->restore($fileId);
        $messenger = service('messenger');
        $messenger->addRestoreSuccess();
        $messenger->dumpMessages();
        return redirect()->to(base_url(sprintf('files/view/%s', $fileId)));
    }
}
