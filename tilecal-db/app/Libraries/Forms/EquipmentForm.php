<?php

namespace App\Library\Forms;
use App\Models\DimModel;

use Exception;

class EquipmentForm extends Form{

    public function initialize()
    {
        $this->fields = [
            'id' => [
                'id' => 'id',
                'required' => true,
                'helpText' => "Equipment's barcode value",
                'fieldType' => 'input',
                'fieldParams' => [
                    'maxlength' => 7,
                    'placeholder' => 'XXXXXXX'
                ]
            ],
            'model_label' => [
                'id' => 'model_label',
                'label' => 'Model',
                'required' => true,
                'helpText' => "Equipment's model",
                'fieldType' => 'input',
                'fieldParams' => [
                    'readOnly' => null
                ]
            ],
            'location' => [
                'id' => 'location',
                'required' => true,
                'fieldType' => 'dropdown',
                'helpText' => "Current equipment location",
                'fieldParams' => [
                    'options' => (new \App\Models\LocationModel())->getFormDropdownOptions()
                ]
            ],
            'status' => [
                'id' => 'status',
                'required' => true,
                'fieldType' => 'dropdown',
                'helpText' => "Current equipment status",
                'fieldParams' => [
                    'options' => (new \App\Models\StatusModel())->getFormDropdownOptions()
                ]
            ],
            'serial' => [
                'id' => 'serial',
                'required' => false,
                'fieldType' => 'input',
                'helpText' => "Equipment serial number",
                'fieldParams' => [
                    'maxlength' => 20
                ]
            ],
            'comments' => [
                'id' => 'comments',
                'required' => false,
                'fieldType' => 'textarea',
                'helpText' => "Possible necessary comments on the equipment situtation.",
                'fieldParams' => [
                    'rows' => 3
                ]
            ],
        ];

        $this->validationRules = [
            "id" => [
                "rules" => [
                    "required",
                    "alpha_numeric",
                    "exact_length[7]",
                    "is_unique[equipments.id]"
                ],
                "errors" => [
                    "required" => "You must supply an id for the equipment.",
                    "alpha_numeric" => "The id must be alphanumeric.",
                    "exact_length" => "The id must have 7 characthers.",
                    "is_unique" => "This id already exists."
                ]
            ],
            "model_label" => [
                "rules" => [
                    "required",
                    "is_not_unique[models.label]",
                ],
                "errors" => [
                    "required" => "You must supply a model for the equipment.",
                    "is_not_unique" => "The given model doesn't exist.",
                ]
            ],
            "location" => [
                "rules" => [
                    "required",
                    "is_not_unique[locations.name]",
                ],
                "errors" => [
                    "required" => "You must supply a location for the equipment.",
                    "is_not_unique" => "The inserted location is not valid",
                ]
            ],
            "status" => [
                "rules" => [
                    "required",
                    "is_not_unique[statuses.name]",
                ],
                "errors" => [
                    "required" => "You must supply a status for the equipment.",
                    "is_not_unique" => "The inserted status is not valid",
                ]
            ],
            "serial" => [
                "rules" => [
                    "max_length[20]"
                ],
                "errors" => [
                    "max_length" => "Serial max size is 20 characthers",
                ]
            ],
            "comments" => [
                "rules" => [
                    "max_length[65535]" //MySQL max text size
                ],
                "errors" => [
                    "max_length" => "Comments max size is 65535 characthers",
                ]
            ],
        ];
    }

    public function getEntity(){
        helper('array');
        if (!$this->isValid){
            throw new Exception('To get the equipment you must validate the form first');
        }
        $equipment = new \App\Entities\Equipment([
            'id' => $this->request->getVar('id'),
            'serial' => $this->request->getVar('serial'),
            'comments' => $this->request->getVar('comments')
        ]);
        $equipment->location_id = $this->getId(
            'name',
            $this->request->getVar('location'),
            new \App\Models\LocationModel()
        );
        $equipment->status_id = $this->getId(
            'name',
            $this->request->getVar('status'),
            new \App\Models\StatusModel()
        );
        $modelLabel = $this->request->getVar('model_label');
        $model = (new \App\Models\ModelModel())->where(sprintf("label = '%s'", $modelLabel))->first();
        d($model);
        $equipment->model_id = $model->id;
        $modelParams = [];
        foreach ($model->params as $param){
            $modelParams[$param] = $this->request->getVar($param);
        }
        $equipment->model_params = $modelParams;
        return $equipment;
    }

    protected function getId(string $fieldName, string $value, DimModel $model){
        $entity = $model->where(sprintf("%s = '%s'", $fieldName, $value))->first();
        return $entity->id;
    }

    protected function commentsEnd(){
        $commentsField = $this->fields['comments'];
        unset($this->fields['comments']);
        $this->fields['comments'] = $commentsField;
    }

    public function asEdit(){
        $this->validationRules['id'] = [
            "rules" => [
                "required",
                "alpha_numeric",
                "exact_length[7]",
                "is_not_unique[equipments.id]"
            ],
            "errors" => [
                "required" => "You must supply an id for the equipment.",
                "alpha_numeric" => "The id must be alphanumeric.",
                "exact_length" => "The id must have 7 characthers.",
                "is_not_unique" => "The given id doesn't exist."
            ]
        ];
        $this->validator->setRules($this->validationRules);
        $this->fields['id']['fieldParams']['readOnly'] = null;
    }
}
