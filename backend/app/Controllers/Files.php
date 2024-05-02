<?php

namespace App\Controllers;

use App\Validation\Validators\FileValidator;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;
use App\Validation\Validator;

class Files extends APIController
{

    /**
     * Get a list of files
     * @return ResponseInterface
     */
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
        helper('response');
        $feature = model('FeatureModel')->findName('api_file_get');
        if (!$feature->isActive()){
            return failServiceUnavailable();
        }

        $inputRules = config('Validation')->fileGetRules;
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];

        $fileModel = model('FileModel')
            ->select('files.id as id')
            ->select('files.name as name')
            ->select('files.path as path')
            ->select('files.description as description')
            ->select('files.created_at as created_at')
            ->select('files.updated_at as updated_at')
            ->select('files.deleted_at as deleted_at')
            ->asArray();
        $this->parseFilterCondition($validInput['id'], 'files.id', $fileModel);
        $this->parseFilterCondition($validInput['name'], 'files.name', $fileModel);
        echo json_encode($validInput);  
        $this->parseDeletion($fileModel, $validInput['onlyDeleted']);

        if (!empty($validInput['relatedEquipmentId'])){
            $fileModel->joinEquipments();
            $fileModel = $this->parseFilterCondition(
                $validInput['relatedEquipmentId'],
                'equipments_to_files.equipment_id',
                $fileModel
            );
        }
        $data = $fileModel->findAll();
        
        return respondQueryResults($data, $inputData, $validInput);
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
        helper('response');
        $inputRules = [
            'id' => [
                '\App\Validation\GeneralRules::permitEmpty' => [],
                '\App\Validation\AppFileRules::idIsSanitized' => []
            ]
        ];
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        $history = model('HistModel')
            ->getFileHist($validInput['id']);
        return respondQueryResults($history, $inputData, $validInput);
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
        helper('response');
        $feature = model('FeatureModel')->findName('file_upload');
        if (!$feature->isActive()){
            return failServiceUnavailable();
        }

        $inputRules = config('Validation')->fileCreateRules;
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        $id = model('FileModel')->insert($validInput);
        return respondCreated($id, $inputData, $validInput);
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
        helper('response');
        $feature = model('FeatureModel')->findName('file_edit');
        if (!$feature->isActive()){
            return failServiceUnavailable();
        }

        $inputRules = config('Validation')->fileUpdateRules;
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        $fileData = $validInput;
        unset($fileData['id']);
        model('FileModel')->update($validInput['id'], $fileData);
        return respondUpdated($validInput['id'], $inputData, $validInput);
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
        helper('response');
        $feature = model('FeatureModel')->findName('file_delete');
        if (!$feature->isActive()){
            return failServiceUnavailable();
        }
        $inputRules = [
            'id' => [
                '\App\Validation\GeneralRules::permitEmpty' => [],
                '\App\Validation\AppFileRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotDeleted' => [
                    'params' => [
                        'table' => 'files',
                        'column' => 'id'
                    ]
                ]
            ],
            'name' => [
                '\App\Validation\GeneralRules::requireWhenFieldIsEmpty' => [
                    'params' => [
                        'field' => 'id'
                    ]
                ],
                '\App\Validation\AppFileRules::nameIsSanitized' => [],
            ],
        ];
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        if (!isset($validInput['id'])){
            $validInput['id'] = model('FileModel')
                ->select('id')
                ->where('name', $validInput['name'])
                ->first()->id;
            
        }
        model('FileModel')->delete($validInput['id']);
        return respondDeleted($validInput['id'], $inputData, $validInput);
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
            return failServiceUnavailable();
        }

        $inputRules = [
            'id' => [
                '\App\Validation\GeneralRules::permitEmpty' => [],
                '\App\Validation\AppFileRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isDeleted' => [
                    'params' => [
                        'table' => 'files',
                        'column' => 'id'
                    ]
                ]
            ],
            'name' => [
                '\App\Validation\GeneralRules::requireWhenFieldIsEmpty' => [
                    'params' => [
                        'field'=> 'id'
                    ]
                ],
                '\App\Validation\AppFileRules::nameIsSanitized' => [],
            ],
        ];
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        if (!isset($validInput['id'])){
            $validInput['id'] = model('FileModel')
                ->select('id')
                ->where('name', $validInput['name'])
                ->first()->id;
            
        }
        model('FileModel')->restore($validInput['id']);
        return respondRestored($validInput['id'], $inputData, $validInput);
    }
}
