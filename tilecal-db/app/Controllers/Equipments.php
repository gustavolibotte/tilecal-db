<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Equipment2FileModel;
use App\Models\EquipmentModel;
use App\Models\FileModel;
use App\Models\ModelModel;

class Equipments extends BaseController
{   
    public function index($modelName=null, $searchDeleted=false)
    {   
        $equipmentModel = new EquipmentModel();
        if (is_null($modelName)){
            $indexData['equipments'] = $equipmentModel->getEquipment(searchDeleted: $searchDeleted);
        }
        else{
            $indexData['equipments'] = $equipmentModel->getEquipmentsWhereModel($modelName, searchDeleted: $searchDeleted);
        }
        $indexData['pageTitle'] = 'Equipment Index';
        $indexData['pageHeader'] = 'Equipment Index';
        return view('equipment/index', $indexData);
    }

    public function deletedIndex(){
        $equipmentModel = new EquipmentModel();
        $indexData['equipments'] = $equipmentModel->getEquipment(searchDeleted: true);
        $indexData['pageTitle'] = 'Deleted Equipment Index';
        return view('equipment/index', $indexData);
    }

    public function view($equipmentId){
        $equipmentModel = new EquipmentModel();
        $equipmentInfo = $equipmentModel->getEquipment($equipmentId);
        $viewData['infoHeader'] = 'Equipment Info';
        $viewData['equipmentInfo'] = $equipmentInfo;
        $histModel = new \App\Models\HistModel();
        $viewData["histories"] = $histModel->getEquipmentHist($equipmentId);
        $viewData['pageHeader'] = sprintf('Equipment: %s', $equipmentInfo->barcode);
        $viewData['pageTitle'] = sprintf('Equipment %s', $equipmentInfo->barcode);
        return view('equipment/view', $viewData);
    }

    public function register($modelName='pmt'){
        helper(['form', 'string', 'url']);

        $formClass = sprintf('\App\Library\Forms\%sForm', snake2LowerCamelCase($modelName));
        $session = \Config\Services::session();
        $equipmentModel = new EquipmentModel();

        if ($this->request->is('post')){
            $form = new $formClass($this->request);
            if ($form->validate()){
                echo "Success";
                $equipment = $form->getEntity();
                $equipmentModel->insert($equipment);
                $session->setFlashdata('success', true);
                return redirect(sprintf('equipments/%s/register', $modelName));
            };
        } else{
            $form = new $formClass();
        }
        
        $modelModel = new ModelModel();
        $model = $modelModel->where(sprintf("name = '%s'", $modelName))->first();
        $registerData['model'] = $model;
        $title = sprintf('Register %s', $model->label);
        $registerData['pageTitle'] = $title;
        $registerData['pageHeader'] = $title;
        $registerData['forms'] = ['Equipment Info'=> $form];
        $registerData['successMessage'] = $session->getFlashdata('success');
        $registerData['formUrl'] = uri_string();
        $registerData['cancelUrl'] = base_url(sprintf('equipments/%s', $modelName));
        return view('equipment/register', $registerData);
    }

    public function getEquipmentInputs(
        $modelLabel,
        $equipment=null,
        $inculdeParent=false,
        $readOnlyID=false){
        
        if (is_null($equipment)){
            $equipment = new \App\Entities\Equipment();
        }

        $equipmentInputs = [
            "id" => [
                "label" => "ID",
                "inputType" => "input",
                "helperInput" => [
                    "name" => "id",
                    "id" => "id",
                    "placeholder" => "Equipment barcode number",
                    "class" => "form-control",
                    "value" => $equipment->id
                ],
                "inputHelp" => null 
            ],
            "model_label" => [
                "label" => "Model",
                "inputType" => "input",
                "helperInput" => [
                    "name" => "model_label",
                    "id" => "model_label",
                    "placeholder" => "Equipment model",
                    "value" => $modelLabel,
                    "class" => "form-control",
                    "readOnly" => null,
                ],
                "inputHelp" => null 
            ],
            "location" => [
                "label" => "Location",
                "inputType" => "input",
                "helperInput" => [
                    "name" => "location",
                    "id" => "location",
                    "placeholder" => "Equipment location",
                    "class" => "form-control",
                    "value" => $equipment->location
                ],
                "inputHelp" => null 
            ],
            "status" => [
                "label" => "Status",
                "inputType" => "input",
                "helperInput" => [
                    "name" => "status",
                    "id" => "status",
                    "placeholder" => "Installed, assembled, broken, etc",
                    "class" => "form-control",
                    "value" => $equipment->status
                ],
                "inputHelp" => null 
            ],
            "serial" => [
                "label" => "Serial",
                "inputType" => "input",
                "helperInput" => [
                    "name" => "serial",
                    "id" => "serial",
                    "placeholder" => "Equipment serial number",
                    "class" => "form-control",
                    "value" => $equipment->serial
                ],
                "inputHelp" => null 
            ]
        ];

        if ($readOnlyID){
            $equipmentInputs["id"]["helperInput"]["readOnly"] = null;
        }

        if ($inculdeParent){
            $parentInput = [
                "parent_id" => [
                    "label" => "parent_id",
                    "inputType" => "input",
                    "helperInput" => [
                        "name" => "parent_id",
                        "id" => "parent_id",
                        "placeholder" => "Parent equipment id",
                        "class" => "form-control",
                        "value" => $equipment->parent_id
                    ],
                    "inputHelp" => null 
                ]
            ];
            $equipmentInputs = array_merge($equipmentInputs, $parentInput);
        }

        return $equipmentInputs;
    }

    public function getInputData($equipmentID,
                                 $model,
                                 $postUrl,
                                 $cancelUrl,
                                 $deleteUrl,
                                 $pageTitle,
                                 $equipmentInputs,
                                 $validationErrors=null){
        $equipmentModel = new EquipmentModel();
        $inputData['enableFileLink'] = false;
        $inputData['relatedEquipments'] = $equipmentModel->getRelatedEquipments($equipmentID);
        $inputData['model'] = $model;
        $inputData["validationErrors"] = $validationErrors;
        $inputData["postUrl"] = $postUrl;
        $inputData["cancelUrl"] = $cancelUrl;
        $inputData['equipmentInputs'] = $equipmentInputs;
        $inputData['pageTitle'] = $pageTitle;
        $inputData['deleteUrl'] = $deleteUrl;

        return $inputData;
    }

    public function edit($modelName, $id){

        helper('form');
        if ($this->request->is('post')){
            return $this->editPost($modelName, $id);
        }
        else{
            return $this->editGet($modelName, $id);
        }
    }

    public function editGet($modelName, $id, $validationErrors=null){

        if(!$this->currentUser->canAlter()){
            return $this->httpErrorView(401);
        }
        $equipmentModel = new EquipmentModel();
        $equipment = $equipmentModel->getEquipment($id);
        $model = [
            "name" => $equipment->modelName,
            "formatted_name" => $equipment->modelLabel
        ];
        $title = sprintf('Editing %s ID %s', $equipment->modelLabel, $equipment->id);
        $headerData = $this->getHeaderData($title);
        $postUrl = base_url(sprintf('equipments/edit/%s/%s', $equipment->modelName, $equipment->id));
        $cancelUrl = base_url(sprintf('equipments/view/%s', $equipment->id));
        $deleteUrl = base_url(sprintf('equipments/delete/%s', $equipment->id));
        $equipmentInputs = $this->getEquipmentInputs(
            modelLabel: $equipment->modelLabel,
            equipment: $equipment,
            inculdeParent: false,
            readOnlyID: true
        );
        $inputData = $this->getInputData($id,
                                         $model,
                                         $postUrl,
                                         $cancelUrl,
                                         $deleteUrl,
                                         $title,
                                         $equipmentInputs,
                                         $validationErrors);

        return view('templates/header', $headerData)
              .view('equipment/input', $inputData)
              .view('templates/footer');
    }

    public function editPost($modelName, $id){

        if(!$this->currentUser->canAlter()){
            return $this->httpErrorView(401);
        }
        $formInputs = $this->request->getPost();
        $formInputs['model_name'] = $this->toSnakeCase($formInputs['model_label']);
        $formInputs['location'] = $this->toSnakeCase($formInputs['location']);
        $formInputs['status'] = $this->toSnakeCase($formInputs['status']);

        if ($this->validateData($formInputs, 'equipmentEditRules')){
            $equipment = new \App\Entities\Equipment($formInputs);
            (new EquipmentModel)->save($equipment);
            return redirect()->to(sprintf("equipments/view/%s", $id));
        }
        else{
            $validationErrors = $this->validator->getErrors();
            return $this->editGet($modelName, $id, $validationErrors);
        }
    }

    public function delete($id){
        $equipmentModel = new EquipmentModel();
        $equipment = $equipmentModel->getEquipment($id);
        $equipmentModel->delete($id);
        $equipment2File = new Equipment2FileModel();
        $fileReferences = $equipment2File->where(sprintf('equipment_id = %s', $id))->findAll();
        foreach ($fileReferences as $reference){
            $equipment2File->delete($reference->id);
        }

        return redirect()->to(sprintf("equipments/%s", $equipment->modelName));
    }
}
