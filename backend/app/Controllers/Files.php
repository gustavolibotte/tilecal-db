<?php

namespace App\Controllers;

use App\Validation\Validators\FileValidator;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Files extends APIController
{

    public function download()
    {
        try{
            return $this->__download__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    public function __download__()
    {
        $feature = model('FeatureModel')->findName('file_download');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
        }

        $inputRules = [
            'id' => [
                'rules' => [
                    'required',
                    'file_id_is_sanitized'
                ],
                'errors' => []
            ]
        ];
        $res = $this->getValidatedData($inputRules);
        if (!$res['valid']){
            return $this->failInvalidParams($res['errors']);
        }
        $inputData = $res['data'];

        $fileModel = model('FileModel');
        $this->parseDeletion($fileModel, false);
        $file = $fileModel->find($inputData['id']);
        if (empty($file)){
            return $this->failNotFound();
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
            return $this->failInternalError($e);
        }
    }

    public function __get__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('api_file_get');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
        }
        $inputRules = [
            'id' => [
                'rules' => [
                    'permit_empty',
                    'file_id_is_sanitized'
                ],
                'errors' => []
            ],
            'name' => [
                'rules' => [
                    'permit_empty',
                    'file_name_is_sanitized'
                ]
            ],
            'onlyDeleted' => [
                'rules' => [
                ],
                'errors' => []
            ],
            'relatedEquipmentId' => [
                'rules' => [
                    'permit_empty',
                    'equipment_id_is_sanitized'
                ]
            ]
        ];

        $res = $this->getValidatedData($inputRules);
        if (!$res['valid']){
            return $this->failInvalidParams($res['errors']);
        }
        $inputData = $res['data'];

        $fileModel = model('FileModel')
            ->select('files.id as id')
            ->select('files.name as name')
            ->select('files.description as description')
            ->select('files.created_at as created_at')
            ->select('files.updated_at as updated_at')
            ->select('files.deleted_at as deleted_at')
            ->select('files.user as user')
            ->asArray();
        $this->parseFilterCondition($inputData['id'], 'files.id', $fileModel);
        $this->parseFilterCondition($inputData['name'], 'files.name', $fileModel);
        $this->parseDeletion($fileModel, !empty($inputData['onlyDeleted']));

        if (!empty($inputData['relatedEquipmentId'])){
            $fileModel->joinEquipments();
            $fileModel = $this->parseFilterCondition(
                $inputData['relatedEquipmentId'],
                'equipments_to_files.equipment_id',
                $fileModel
            );
        }
        $data = $fileModel->findAll();
        
        return $this->respondQueryResults($data);
    }

    public function getHist(): ResponseInterface
    {
        try{
            return $this->__getHist__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    public function __getHist__(): ResponseInterface
    {
        $inputRules = [
            'id' => [
                'rules' => [
                    'required',
                    'file_id_is_sanitized'
                ]
            ]
        ];
        $res = $this->getValidatedData($inputRules);
        if (!$res['valid']){
            return $this->failInvalidParams($res['errors']);
        }
        $inputData = $res['data'];
        $history = model('HistModel')
            ->getFileHist($inputData['id']);
        return $this->respondQueryResults($history);
    }

    
    public function post(): ResponseInterface
    {
        try{
            return $this->__post__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __post__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('file_upload');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
        }

        $validator = (new FileValidator())->addRequest($this->request);
        if ($validator->validate()){
            $file = $validator->getValidatedData();
            model('FileModel')->insert($file);
            return $this->respondCreated($file->name);
        }
        return $this->failValidationErrors($validator->getErrors());
        
    }

    public function put(): ResponseInterface
    {
        try{
            return $this->__put__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    public function __put__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('file_edit');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
        }

        $inputRules = [
            'id' => [
                'rules' => [
                    'required',
                    'file_id_is_sanitized',
                    'is_not_unique[files.id]'
                ],
                'errors' => [
                    'required' => 'File ID is required',
                    'is_not_unique' => 'File with ID "{value}" doesn\'t exist.'
                ]
            ]
        ];
        $res = $this->getValidatedData($inputRules);
        if (!$res['valid']){
            return $this->failInvalidParams($res['errors']);
        }
        $inputData = $res['data'];
        
        $validator = (new FileValidator())
            ->asEdit()
            ->addRequest($this->request);
        if ($validator->validate()){
            $file = $validator->getValidatedData();
            model('FileModel')->update($inputData['id'], $file);
            return $this->respondUpdated();
        }
        return $this->failValidationErrors($validator->getErrors());
    }

    public function delete(): ResponseInterface
    {
        try{
            return $this->__delete__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __delete__(): ResponseInterface
    {

        $feature = model('FeatureModel')->findName('file_delete');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
        }
        $inputRules = [
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
        $res = $this->getValidatedData($inputRules);

        if (!$res['valid']){
            return $this->failInvalidParams($res['errors']);
        }

        model('FileModel')->delete($res['data']['id']);
        return $this->respondDeleted();
    }

    public function restore(): ResponseInterface
    {
        try{
            return $this->__restore__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __restore__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('file_restore');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
        }

        $inputRules = [
            'id' => [
                'rules' => [
                    'required',
                    'file_id_is_sanitized',
                    'is_not_unique[files.id]'
                ],
                'errors' => [
                    'required' => 'File ID is required',
                    'is_not_unique' => 'File with ID "{value}" doesn\'t exist.'
                ]
            ]
        ];
        $res = $this->getValidatedData($inputRules);

        if (!$res['valid']){
            return $this->failInvalidParams($res['errors']);
        }

        model('FileModel')->restore($res['data']['id']);
        return $this->respondDeleted();
    }

    public function downloadHist(): ResponseInterface
    {
        try{
            return $this->__downloadHist__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    public function __downloadHist__()
    {
        $feature = model('FeatureModel')->findName('file_download');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
        }
        $inputRules = [
            'id' => [
                'rules' => [
                    'required',
                    'hist_id_is_sanitized'
                ],
                'errors' => []
            ]
        ];
        $res = $this->getValidatedData($inputRules);
        if (!$res['valid']){
            return $this->failInvalidParams($res['errors']);
        }
        $inputData = $res['data'];

        $fileHistArray = model('HistModel')->getOlderFile($inputData['id']);
        if (empty($fileHistArray)){
            return $this->failNotFound();
        }
        $fileHist = new \App\Entities\File($fileHistArray);
        return $this->getFileDownloadResponse($fileHist);
    }
}
