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

    public function delete(): ResponseInterface
    {
        try{
            return $this->__delete__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    protected function __delete__(): ResponseInterface
    {

        $feature = model('FeatureModel')->findName('file_delete');
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

        model('FileModel')->delete($data['id']);
        return $this->buildAPIResponse(200, [], 'Deleted file with success.');
    }

    public function restore(): ResponseInterface
    {
        try{
            return $this->__restore__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    protected function __restore__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('file_restore');
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

        model('FileModel')->restore($data['id']);
        return $this->buildAPIResponse(200, [], 'Deleted file with success.');
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
