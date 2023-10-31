<?php

namespace App\Controllers;

use App\Library\Forms\EquipmentBatchRegisterForm;
use App\Library\Forms\EquipmentForm;
use App\Library\Writers\CSVFileWriter;
use App\Library\Readers\CSVFileReader;
use App\Models\EquipmentModel;
use App\Models\EquipmentTypeModel;
use CodeIgniter\HTTP\ResponseInterface;

class Equipments extends APIController
{

    public function index($typeName=null)
    {   
        $typeName = $this->request->getVar('type_name');
        if (!empty($this->request->getVar('deleted'))){
            $indexData['headerClass'] = 'text-danger';
            $pageTitle = 'Deleted Equipment Index';
        } elseif (!empty($typeName)){
            $indexData['typeName'] = $typeName;
            $equipmentType = model('EquipmentTypeModel')
                ->select('label')
                ->where('name', $typeName)
                ->first();
            $pageTitle = sprintf('%s Index', $equipmentType->label);
        } else{
            $pageTitle = 'Equipment Index';
        }
            $indexData['pageTitle'] = $pageTitle;
            $indexData['pageHeader'] = $pageTitle;
        
        return default_view('equipment/index', $indexData);
    }

    public function view(){
        helper(['app_form', 'form']);
        $equipmentId = $this->request->getVar('id');
        $equipmentModel = model('EquipmentModel');
        $equipment = $equipmentModel->withDeleted()->getEquipment($equipmentId);

        if (is_null($equipment)){
            return redirect('notfound');
        } elseif ($equipment->isDeleted() && !$this->user->isAuthorized('delete')){
            return redirect('deleted-entry');
        }

        $viewData['infoHeader'] = 'Equipment Info';
        $viewData['entity'] = $equipment;
        $viewData["histories"] = model('HistModel')->getEquipmentHist($equipmentId);
        $viewData['pageHeader'] = sprintf('Equipment: %s', $equipment->id);

        if ($equipment->isDeleted()){
            $viewData['pageHeader'] = 'Deleted ' . $viewData['pageHeader'];
            $viewData['headerClass'] = 'text-danger';
        }
        $viewData['pageTitle'] = $viewData['pageHeader'];

        $viewData['editable'] = $this->user->isAuthorized('edit') && !$equipment->isDeleted();
        if ($viewData['editable']){
            $viewData['editForm'] = EquipmentForm::getTypeForm($equipment->type_name);
            $viewData['editForm']->asEdit();
            $viewData['editForm']->setFieldValues($equipment->toArrayWithTypeParams());
            $parentRelation = $equipmentModel->getParentRelation($equipment->id);
            if (!empty($parentRelation)){
                $viewData['editForm']->setFieldValues([
                    'parent_id' => $parentRelation->parent_equip_id,
                    'slot_id' => $parentRelation->slot_id
                ]);
            }
            $viewData['editUrl'] = base_url(sprintf('equipments/%s/edit/%s', $equipment->type_name, $equipment->id));
        }

        $viewData['deletable'] = $this->user->isAuthorized('delete') && !$equipment->isDeleted();
        if ($viewData['deletable']){
            $viewData['deleteUrl'] = base_url(sprintf('equipments/delete/%s', $equipment->id));
        }

        $viewData['restorable'] = $this->user->isAuthorized('delete') && $equipment->isDeleted();
        if ($viewData['restorable']){
            $viewData['restoreUrl'] = base_url(sprintf('equipments/restore/%s', $equipment->id));
        }

        return default_view('equipment/view', $viewData);
    }

    public function batchRegister()
    {
        helper(['form', 'url', 'app_form', 'path']);
        $messenger = \Config\Services::messenger();
        if ($this->request->is('post')){
            $form = new EquipmentBatchRegisterForm($this->request);
            if ($form->validate()){
                $formData = $form->getValidatedData();
                $csvFile = new CSVFileReader($formData['file']->getTempName());
                $equipmentForm = EquipmentForm::getTypeForm($formData['type_name']);
                $filepath = $this->validateEquipmentFile($csvFile, $equipmentForm);
                if (!empty($filepath)) {
                    $splitted = splitPath($filepath);
                    $messenger->addMessage(sprintf(
                        'Found errors on the file. ' .
                        '<a href="%s">Download validation log.</a>',
                        url_to('download_validation_log', $splitted['tail'])
                        ),
                        'danger'
                    );
                }
            } else{
                foreach ($form->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                }
            }
        } else{
            $form = new EquipmentBatchRegisterForm();
        }
        $viewData['forms'] = ['' => $form];
        $viewData['formUrl'] = uri_string();
        $viewData['pageTitle'] = 'Batch Register';
        $viewData['pageHeader'] = 'Batch Equipment Register';
        $viewData['cancelUrl'] = url_to('equipment_index');
        return default_view('layouts/register_single_tab', $viewData);

    }

    public function register($typeName='pmt'){
        helper(['form', 'url', 'app_form']);

        $equipmentModel = model('equipmentModel');
        $messenger = service('messenger');
        if ($this->request->is('post')){
            $form = EquipmentForm::getTypeForm($typeName, $this->request);
            if ($form->validate()){
                $equipmentModel = model('equipmentModel');
                $equipment = $form->getValidatedData();
                $equipmentModel->insert($equipment);
                $messenger->addRegisterSuccess();
                $messenger->dumpMessages();
                return redirect()->to(sprintf('equipments/%s/register', $typeName));
            } else {
                foreach ($form->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                }
                $messenger->dumpMessages();
            };
        } else{
            $form = EquipmentForm::getTypeForm($typeName);
        }
        $equipmentTypeModel = new EquipmentTypeModel();
        $type = $equipmentTypeModel->where(sprintf("name = '%s'", $typeName))->first();
        $registerData['model'] = $type;
        $title = sprintf('Register %s', $type->label);
        $registerData['pageTitle'] = $title;
        $registerData['pageHeader'] = $title;
        $registerData['forms'] = ['Equipment Info'=> $form];
        $registerData['formUrl'] = uri_string();
        $registerData['cancelUrl'] = base_url(sprintf('equipments/%s', $typeName));
        
        return default_view('equipment/register', $registerData);
    }

    public function edit($equipmentId, $typeName)
    {
        $form = EquipmentForm::getTypeForm($typeName, $this->request);
        $form->asEdit();
        $messenger = service('messenger');
        if ($form->validate()){
            $equipmentModel = model('EquipmentModel');
            $equipment = $form->getValidatedData();
            $equipmentModel->update($equipmentId, $equipment);
            $messenger->addMessage(
                sprintf('Edited equipment %s with success', $equipment->id),
                'success'
            );
        } else{
            foreach ($form->getErrors() as $errorMsg){
                $messenger->addMessage($errorMsg, 'danger');
            }
        }
        $messenger->dumpMessages();
        return redirect()->to(base_url(sprintf('equipments/view/%s', $equipmentId)));
    }

    public function delete(string $equipmentId)
    {
        $equipmentModel = model('EquipmentModel');
        $equipmentModel->delete($equipmentId);
        $messenger = service('messenger');
        $messenger->addDeleteSuccess();
        $messenger->dumpMessages();
        if ($this->user->isAuthorized('delete')){
            return redirect()->to(base_url(sprintf('equipments/view/%s', $equipmentId)));
        } else {
            return redirect('equipments');
        }
    }

    public function restore(string $equipmentId)
    {
        $equipmentModel = new EquipmentModel();
        $equipmentModel->restore($equipmentId);
        service('messenger')->addRestoreSuccess();
        return redirect()->to(base_url(sprintf('equipments/view/%s', $equipmentId)));
    }

    public function __get__(): ResponseInterface
    {
        $equipmentModel = model('EquipmentModel')->asArray();
        $equipmentModel = $this->parseFilterCondition('id', 'equipments.id', $equipmentModel);
        $equipmentModel = $this->parseFilterCondition('type_name', 'equipment_types.name', $equipmentModel);

        if (!empty($this->request->getVar('relatedFileId'))){
            $equipmentModel = $this->parseFilterCondition(
                'relatedFileId',
                'equipments_to_files.file_id',
                $equipmentModel->joinFiles()
            );
        }

        $deleteMsg = $this->checkDeletion();
        if (!empty($this->request->getVar('onlyDeleted'))){
            if (!empty($deleteMsg)){
                return $this->failForbidden(message: $deleteMsg);
            }
            $equipmentModel = $equipmentModel->onlyDeleted();
        } elseif (!empty($this->request->getVar('withDeleted'))){
            if (!empty($deleteMsg)){
                return $this->failForbidden(message: $deleteMsg);
            }
            $equipmentModel = $equipmentModel->withDeleted();
        }

        $this->parseOrdering('orderBy', $equipmentModel->getTable(), $equipmentModel);
        $this->parseReturnType('returnType', $equipmentModel);

        $relatedEquipmentId = ($this->request->getVar('relatedEquipmentId'));
        if (empty($relatedEquipmentId)){
            $data = $equipmentModel->findAll();
        } else {
            $data = $equipmentModel->getRelatedEquipments($relatedEquipmentId);
        }
        $res = [
            'status' => 'success'
        ];
        $res['data'] = empty($data)? []: $data;
        return $this->respond($res, 200);
    }

    protected function validateEquipmentFile($fileReader, $form)
    {
        helper('filesystem');
        $foundError = false;
        $logFilepath = tempnam(config('Paths')->validationLogsDirectory, '');
        $fileWriter = (new CSVFileWriter($logFilepath))->open();
        $equipmentModel = model('EquipmentModel');
        $this->db->transBegin();
        foreach ($fileReader as $iRow=>$row){
            $form->setFieldValues($row);
            if ($form->validate()){
                $equipmentModel->insert($row);
            } else{
                $foundError = true;
                foreach($form->getErrors() as $field=>$errorMsg){
                    $fileWriter->addLine([
                        $iRow,
                        $field,
                        $errorMsg
                    ]);
                }
            }
        }
        $fileWriter->close();
        if ($foundError){
            $this->db->transRollback();
            return $logFilepath;
        } else{
            $this->db->transCommit();
            delete_files($logFilepath);
            return null;
        }
    }
}