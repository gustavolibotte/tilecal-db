<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Library\Forms\EquipmentForm;
use App\Models\EquipmentModel;
use App\Models\EquipmentTypeModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Model;

class Equipments extends BaseController
{
    use ResponseTrait;

    public function index($typeName=null)
    {   
        $indexData['pageTitle'] = 'Equipment Index';
        $indexData['pageHeader'] = 'Equipment Index';
        $indexData['typeName'] = $this->request->getVar('type_name');
        return default_view('equipment/index', $indexData);
    }

    public function deletedIndex()
    {   
        $indexData['pageTitle'] = 'Deleted Equipment Index';
        $indexData['pageHeader'] = 'Deleted Equipment Index';
        $indexData['headerClass'] = 'text-danger';
        return default_view('equipment/deleted_index', $indexData);
    }

    public function view($equipmentId){
        helper(['app_form', 'form']);
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

    public function register($typeName='pmt'){
        helper(['form', 'url', 'app_form']);

        $equipmentModel = new EquipmentModel();
        $messenger = service('messenger');
        if ($this->request->is('post')){
            $form = EquipmentForm::getTypeForm($typeName, $this->request);
            $fieldValues = $form->getFieldValues();
            if ($form->validate()){
                $equipmentModel = new EquipmentModel();
                $equipment = $form->getEntity();
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
            $equipment = $form->getEntity();
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

    public function getEquipments()
    {
        if (!$this->request->isAJAX()){
            return $this->fail('Requests must be AJAX.', 400);
        }
        $equipmentModel = model('EquipmentModel')->fullQuery()->asArray();
        $equipmentModel = $this->addFilterCondition('id', 'equipments.id', $equipmentModel);
        $equipmentModel = $this->addFilterCondition('type_name', 'equipment_types.name', $equipmentModel);
        $data = $equipmentModel->findAll();
        $res = [
            'status' => 'success'
        ];
        $res['data'] = empty($data)? []: $data;
        return $this->respond($res, 200);
    }

    protected function addFilterCondition(string $varName, string $colName, Model $model): Model
    {
        $var = $this->request->getVar($varName);
        if (!empty($var)){
            if (is_array($var)){
                $model = $model->whereIn($colName, $var);
            } else{
                $model = $model->where($colName, $var);
            }
        }
        return $model;
    }
}