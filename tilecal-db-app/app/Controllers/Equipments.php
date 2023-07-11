<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;
use App\Models\FileModel;
use App\Models\ModelModel;

class Equipments extends BaseController
{
    public function index($modelName=null)
    {   
        $equipmentModel = new EquipmentModel();
        $headerData['pageTitle'] = 'Equipment Index';
        if (is_null($modelName)){
            $indexData['equipments'] = $equipmentModel->getEquipment();
        }
        else{
            $indexData['equipments'] = $equipmentModel->getEquipmentsWhereModel($modelName);
        }
        return view('templates/header', $headerData)
              .view('equipment/index', $indexData)
              .view('templates/footer');
    }

    public function view($id){
        $viewData = $this->getViewData($id);
        $headerData['pageTitle'] = sprintf('Equipment %s', $id);
        return view('templates/header', $headerData)
              .view('equipment/view', $viewData)
              .view('templates/footer');
    }

    public function getViewData($id){

        $equipmentModel = new EquipmentModel();
        $viewData['relatedEquipments'] = $equipmentModel->getEquipment();
        $viewData['equipmentInfo'] = $equipmentModel->getEquipment($id);

        $fileModel = new FileModel();
        $viewData['files'] = $fileModel->getBindedFilesInfo($id);

        return $viewData;
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

    public function getInputData($model,
                                 $postUrl,
                                 $cancelUrl,
                                 $pageTitle,
                                 $equipmentInputs,
                                 $validationErrors=null){
        
        $inputData['enableFileLink'] = false;
        $inputData['relatedEquipments'] = (new EquipmentModel())->getSideTable();
        $inputData['model'] = $model;
        $inputData["validationErrors"] = $validationErrors;
        $inputData["postUrl"] = $postUrl;
        $inputData["cancelUrl"] = $cancelUrl;
        $inputData['equipmentInputs'] = $equipmentInputs;
        $inputData['pageTitle'] = $pageTitle;

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

        $equipmentModel = new EquipmentModel();
        $equipment = $equipmentModel->getEquipment($id);
        $model = [
            "name" => $equipment->modelName,
            "formatted_name" => $equipment->modelLabel
        ];
        $title = sprintf('%s Edit', $equipment->modelLabel);
        $headerData = $this->getHeaderData($title);
        $postUrl = base_url(sprintf('equipments/edit/%s/%s', $equipment->modelName, $equipment->id));
        $cancelUrl = base_url(sprintf('equipments/view/%s', $equipment->id));
        $equipmentInputs = $this->getEquipmentInputs(
            modelLabel: $equipment->modelLabel,
            equipment: $equipment,
            inculdeParent: false,
            readOnlyID: true
        );
        $inputData = $this->getInputData($model,
                                         $postUrl,
                                         $cancelUrl,
                                         $title,
                                         $equipmentInputs,
                                         $validationErrors);

        return view('templates/header', $headerData)
              .view('equipment/input', $inputData)
              .view('templates/footer');
    }

    public function editPost($modelName, $id){

        $formInputs = $this->request->getPost();
        $formInputs['model_name'] = $this->toSnakeCase($formInputs['model_label']);
        $formInputs['location'] = $this->toSnakeCase($formInputs['location']);
        $formInputs['status'] = $this->toSnakeCase($formInputs['status']);

        if ($this->validateData($formInputs, 'equipmentEditRules')){
            $equipment = new \App\Entities\Equipment($formInputs);
            (new EquipmentModel)->save($equipment);
            return $this->view($id);
        }
        else{
            $validationErrors = $this->validator->getErrors();
            d($formInputs);
            return $this->editGet($modelName, $id, $validationErrors);
        }
    }
}
