<?php

namespace App\Controllers;

use App\Library\Forms\FileForm;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class Files extends APIController
{
    public function index()
    {
        if (empty($this->request->getVar('onlyDeleted'))){
            $pageTitle = 'File Index';
        } else{
            $indexData['headerClass'] = 'text-danger';
            $pageTitle = 'Deleted File Index';
        }
        $indexData['pageTitle'] = $pageTitle;
        $indexData['pageHeader'] = $pageTitle;
        return default_view('files/index', $indexData);
    }

    public function view($fileId){
        helper('form');
        $fileModel = model('FileModel');
        $file = $fileModel->withDeleted()->find($fileId);
        if (is_null($file)){
            return $this->notFound();
        } elseif ($file->isDeleted() && !$this->user->isAuthorized('delete')){
            return $this->deletedEntry();
        }

        $viewData['entity'] = $file;
        $viewData['pageHeader'] = sprintf('File %s', $file->name);
        $viewData['infoHeader'] = 'File Info';
        $viewData['histories'] = model('HistModel')->getFileHist($file->id);
        d($viewData['histories']);

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
            // $viewData['deleteUrl'] = base_url(sprintf('files/delete/%d', $file->id));
            $viewData['deleteUrl'] = url_to('file_delete', $file->id);
        }

        $viewData['restorable'] = $this->user->isAuthorized('delete') && $file->isDeleted();
        if ($viewData['restorable']){
            // $viewData['restoreUrl'] = base_url(sprintf('files/restore/%s', $file->id));
            $viewData['restoreUrl'] = url_to('file_restore', $file->id);
        }

        return default_view('files/view', $viewData);
    }

    public function download($fileId){
        $fileModel = model('FileModel');
        if ($this->user->isAuthorized('delete')){
            $fileModel->withDeleted();
        }
        $file = $fileModel->find($fileId);
        if (is_null($file)){
            return $this->notFound();
        }
        $response = $this->response
            ->download($file->path, null, true)
            ->setFileName($file->name)
            ->setContentType($file->mime);
        return $response;
    }

    public function upload(){
        helper(['form', 'string', 'url']);
        $messenger = service('messenger');

        if ($this->request->is('post')){
            $form = new FileForm($this->request);
            if ($form->validate()){
                $file = $form->getValidatedData();
                $fileModel = model('FileModel');
                $fileModel->insert($file);
                $messenger->addMessage('File uploaded with success', 'success');
                return redirect('file_upload');
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
        // $registerData['cancelUrl'] = base_url('files');
        $registerData['cancelUrl'] = url_to('file_index');
        $messenger->dumpMessages();

        return default_view('files/upload', $registerData);
    }

    public function delete($fileId)
    {
        $fileModel = model('FileModel');
        $fileModel->delete($fileId);
        $messenger = service('messenger');
        $messenger->addDeleteSuccess();
        $messenger->dumpMessages();
        if ($this->user->isAuthorized('delete')){
            return redirect()->route('file_view', [$fileId]);

        } else {
            return redirect()->route('file_index');
        }
    }

    public function restore($fileId)
    {
        $fileModel = model('FileModel');
        $fileModel->restore($fileId);
        $messenger = service('messenger');
        $messenger->addRestoreSuccess();
        $messenger->dumpMessages();
        return redirect()->route('file_view', [$fileId]);
    }

    public function __get__(): ResponseInterface
    {
        $fileModel = model('FileModel')->asArray();
        $fileModel = $this->parseFilterCondition('id', 'files.id', $fileModel);
        $fileModel = $this->parseFilterCondition('name', 'files.name', $fileModel);
        $this->parseOrdering('orderBy', 'files', $fileModel);
        $this->parseReturnType('returnType', $fileModel);

        $deleteMsg = $this->checkDeletion();
        if (!empty($this->request->getVar('onlyDeleted'))){
            if (!empty($deleteMsg)){
                return $this->failForbidden(message: $deleteMsg);
            }
            $fileModel = $fileModel->onlyDeleted();
        } elseif (!empty($this->request->getVar('withDeleted'))){
            if (!empty($deleteMsg)){
                return $this->failForbidden(message: $deleteMsg);
            }
            $fileModel = $fileModel->withDeleted();
        }

        if (!empty($this->request->getVar('relatedEquipmentId'))){
            $fileModel = $this->parseFilterCondition('relatedEquipmentId',
                'equipments_to_files.equipment_id',
                $fileModel->joinEquipments()
            );
        }

        $res = ['status' => 'sucesss'];
        $data = $fileModel->findAll();
        $res['data'] = empty($data)? []: $data;
        return $this->respond($res, 200);
    }

    public function downloadValidationLog($filename)
    {
        $filepath = sprintf('%s/%s', config('Paths')->validationLogsDirectory, $filename);
        $response = $this->response
            ->download($filepath, null, true)
            ->setFileName('validation_log.csv')
            ->setContentType('text/csv');
        return $response;
    }
}
