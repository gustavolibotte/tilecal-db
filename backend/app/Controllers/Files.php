<?php

namespace App\Controllers;

use App\Library\Forms\FileForm;
use App\Validation\Validators\FileValidator;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\ResponseInterface;
use ValueError;
use Throwable;

class Files extends APIController
{
    public function index()
    {

        $feature = model('FeatureModel')->findName('file_index');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

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

    public function view($fileId)
    {

        $feature = model('FeatureModel')->findName('file_view');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

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

        if ($file->isDeleted()){
            $viewData['pageHeader'] = 'Deleted ' . $viewData['pageHeader'];
            $viewData['headerClass'] = 'text-danger';
        }
        $viewData['pageTitle'] = $viewData['pageHeader'];

        $viewData['editable'] =  $this->user->isAuthorized('edit') && (!$file->isDeleted());
        if ($viewData['editable']){
            $viewData['editForm'] = new FileForm();
            $viewData['editUrl'] = url_to('file_update' , $file->id);
        }
        $viewData['deletable'] = $this->user->isAuthorized('delete') && (!$file->isDeleted());
        if ($viewData['deletable']){
            $viewData['deleteUrl'] = url_to('file_delete', $file->id);
        }

        $viewData['restorable'] = $this->user->isAuthorized('delete') && $file->isDeleted();
        if ($viewData['restorable']){
            $viewData['restoreUrl'] = url_to('file_restore', $file->id);
        }

        return default_view('files/view', $viewData);
    }

    public function update($fileId)
    {
        $feature = model('FeatureModel')->findName('file_edit');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }
        $messenger = service('messenger');

        $validator = (new FileValidator())
            ->asEdit()
            ->setFieldValues(['id' => $fileId])
            ->addRequest($this->request);
        if ($validator->validate()){
            $file = $validator->getValidatedData();
            model('FileModel')->update($fileId, $file);
            $messenger->addMessage('Updated file data with success', 'success');
        } else{
            foreach ($validator->getErrors() as $errorMsg){
                $messenger->addMessage($errorMsg, 'danger');
            }
        }
        $messenger->dumpMessages();
        return $this->view($fileId);
    }

    public function download($fileId)
    {
        $feature = model('FeatureModel')->findName('file_download');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        $fileModel = model('FileModel');
        if ($this->user->isAuthorized('delete')){
            $fileModel->withDeleted();
        }
        $file = $fileModel->find($fileId);
        if (is_null($file)){
            return $this->notFound();
        }
        return $this->getFileDownloadResponse($file);
    }

    public function getFileDownloadResponse($file)
    {
        $extension = '.' . $file->fileObj->guessExtension();
        $filename = (str_ends_with($file->name, $extension))? $file->name: $file->name . $extension;
        return $this->response
        ->download($file->path, null, true)
        ->setFileName($filename)
        ->setContentType($file->mime);
    }

    public function upload()
    {
        $feature = model('FeatureModel')->findName('file_upload');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        helper(['form', 'string', 'url']);
        $messenger = service('messenger');

        if ($this->request->is('post')){
            $validator = (new FileValidator())->addRequest($this->request);
            if ($validator->validate()){
                $file = $validator->getValidatedData();
                model('FileModel')->insert($file);
                $messenger->addMessage('File uploaded with success', 'success');
                $messenger->dumpMessages();
                return redirect('file_upload');
            } else {
                foreach ($validator->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                };
                $form = new FileForm($this->request);
            }
        } else{
            $form = new FileForm();
        }
        
        $title = 'Upload File';
        $registerData['pageTitle'] = $title;
        $registerData['pageHeader'] = $title;
        $registerData['forms'] = ['File Info'=> $form];
        $registerData['formUrl'] = uri_string();
        $registerData['cancelUrl'] = url_to('file_index');
        $messenger->dumpMessages();

        return default_view('files/upload', $registerData);
    }

    public function delete($fileId)
    {

        $feature = model('FeatureModel')->findName('file_delete');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

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
        $feature = model('FeatureModel')->findName('file_restore');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        $fileModel = model('FileModel');
        $fileModel->restore($fileId);
        $messenger = service('messenger');
        $messenger->addRestoreSuccess();
        $messenger->dumpMessages();
        return redirect()->route('file_view', [$fileId]);
    }

    public function get(): ResponseInterface
    {
        try{
            return $this->__get__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    public function __get__(): ResponseInterface
    {

        $feature = model('FeatureModel')->findName('api_file_get');
        if (!$feature->isActive()){
            return $this->fail("", 503);
        }

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
        $data = $fileModel->findAll();
        
        return $this->buildAPIResponse(200, empty($data)? []: $data);
    }

    public function getHist(): ResponseInterface
    {
        try{
            return $this->__getHist__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    public function __getHist__(): ResponseInterface
    {
        $id = $this->request->getVar('id');
        if (empty($id)){
            throw new ValueError('An id value must be given.');
        }
        $history = model('HistModel')
            ->getFileHist($id);
        return $this->buildAPIResponse(200, $history);
    }

    
    public function post(): ResponseInterface
    {
        try{
            return $this->__post__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    protected function __post__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('file_upload');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        $validator = (new FileValidator())->addRequest($this->request);
        if ($validator->validate()){
            $file = $validator->getValidatedData();
            model('FileModel')->insert($file);
            return $this->buildAPIResponse(200, [], 'File uploaded with success.');
        }
        $errors = $validator->getErrors();
        return $this->fail($errors, 400, 'Invalid data.');
        
    }

    public function put(): ResponseInterface
    {
        try{
            return $this->__put__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    public function __put__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('file_edit');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        $data = ['id' => $this->request->getVar('id')];
        $rules = [
            'id' => [
                'rules' => [
                    'required',
                    'is_not_unique[files.id]'
                ],
                'errors' => [
                    'required' => 'File ID is required',
                    'is_not_unique' => 'File with ID "{value}" doesn\'t exist.'
                ]
            ]
        ];

        if (!$this->validateData($data, $rules)){
            $errors = $this->validator->getErrors();
            return $this->fail([], 400, $errors['id']);
        }
        
        $validator = (new FileValidator())
            ->asEdit()
            ->addRequest($this->request);
        if ($validator->validate()){
            $file = $validator->getValidatedData();
            model('FileModel')->update($data['id'], $file);
            return $this->buildAPIResponse(200, [], 'Altered file with success.');
        }
        $errors = $validator->getErrors();
        return $this->fail($errors, 400, 'Invalid data.');
    }

    public function downloadHist()
    {
        $feature = model('FeatureModel')->findName('file_download');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        $fileHIstId = $this->request->getVar('id');
        $fileHistArray = model('HistModel')->getOlderFile($fileHIstId);
        $fileHist = new \App\Entities\File($fileHistArray);
        return $this->getFileDownloadResponse($fileHist);
    }
}
