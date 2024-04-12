<?php

namespace App\Controllers;

use App\Validation\Validator;
use App\Validation\Validators\EquipmentValidator;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Equipments extends APIController
{
    protected array $existingIdRule = [
        'id' => [
            'rules' => [
                'required',
                'equipment_id_is_sanitized',
                'is_not_unique[equipments.id]'
            ]
        ]
    ];

    public function get(): ResponseInterface
    {
        try {
            return $this->__get__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    protected function __get__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('api_equipment_get');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }

        $inputRules = [
            'id' => [
                'permitEmpty' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => []
            ],
            'type_name' => [
                'permitEmpty' => [],
                '\App\Validation\EquipmentTypeRules::nameIsSanitized' => []
            ],
            'relatedFileId' => [
                'permitEmpty' => [],
                '\App\Validation\FileRules::idIsSanitized' => []
            ],
            'relatedEquipmentId' => [
                'permitEmpty' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => []
            ],
            'onlyDeleted' => [
                'permitEmpty' => []
            ]
        ];

        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return $this->failValidationErrors($validationRes['errors']);
        }
        $validInput = $validationRes['validatedData'];

        $equipmentModel = model('EquipmentModel')->asArray();
        $equipmentModel = $this->parseFilterCondition($validInput['id'], 'equipments.id', $equipmentModel);
        $equipmentModel = $this->parseFilterCondition($validInput['type_name'], 'equipment_types.name', $equipmentModel);

        if (!empty($validInput['relatedFileId'])) {
            $equipmentModel->joinFiles();
            $equipmentModel = $this->parseFilterCondition(
                $validInput['relatedFileId'],
                'equipments_to_files.file_id',
                $equipmentModel
            );
        }

        $equipmentModel = $this->parseDeletion($equipmentModel, !empty($validInput['onlyDeleted']));

        if (empty($validInput['relatedEquipmentId'])) {
            $data = $equipmentModel->fullQuery()->findAll();
        } else {
            $data = $equipmentModel->getRelatedEquipments($validInput['relatedEquipmentId']);
        }
        helper('app_array');
        foreach ($data as &$sample) {
            $sample['type_params'] = json_decode($sample['type_params']);
        }
        $env = \Config\Services::environment();
        if ($env->isLocal() || $env->isDev()) {
            return $this->buildAPIResponse(
                200,
                $data,
                metadata: [
                    'validInput' => $validInput,
                    'input' => $inputData
                ]
            );
        }
        return $this->respondQueryResults($data);
    }

    public function post(): ResponseInterface
    {
        try {
            return $this->__post__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    protected function __post__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_register');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }

        $rules = [
            'type_name' => [
                'rules' => [
                    'required',
                    'equipment_type_name_is_sanitized',
                    'is_not_unique[equipment_types.name]'
                ]
            ]
        ];
        $data = $this->getRequestValuesFromRules($rules);

        if (!$this->validateData($data, $rules)) {
            $errors = $this->validator->getErrors();
            return $this->failValidationErrors($errors);
        }

        $validator = EquipmentValidator::getFromTypeName($data['type_name']);
        $validator->addRequest($this->request);
        if ($validator->validate()) {
            $equipment = $validator->getValidatedData();
            $equipmentModel = model('EquipmentModel');
            $equipmentModel->insert($equipment);
            return $this->respondCreated($equipment->id);
        } else {
            $errors = $validator->getErrors();
            return $this->failValidationErrors($errors);
        }
    }

    public function getHist(): ResponseInterface
    {
        try {
            return $this->__getHist__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    protected function __getHist__(): ResponseInterface
    {
        $sanitationRules = [
            'id' => [
                'rules' => [
                    'required',
                    'equipment_id_is_sanitized'
                ]
            ]
        ];
        $inputData = $this->getRequestValuesFromRules($sanitationRules);
        if (!$this->validateData($inputData, $sanitationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        helper('equipment');
        $inputData['id'] = preprocessEquipmentId($inputData['id'] ?? '');
        $history = model('HistModel')
            ->getEquipmentHist($inputData['id']);
        return $this->respondQueryResults($history);
    }

    public function put(): ResponseInterface
    {
        try {
            return $this->__put__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    protected function __put__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_edit');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }
        $res = $this->getValidatedData($this->existingIdRule);
        if (!$res['valid']) {
            return $this->failValidationErrors($res['errors']);
        }
        $data = $res['data'];
        $equipmentModel = model('EquipmentModel');
        $equipment = $equipmentModel
            ->asArray()
            ->fullQuery()
            ->find($data['id']);
        $validator = EquipmentValidator::getFromTypeName($equipment['type_name'])
            ->addRequest($this->request)
            ->asEdit();
        if (!$validator->validate()) {
            $errors = $validator->getErrors();
            return $this->failValidationErrors($errors);
        }
        $updatedEquipment = $validator->getValidatedData();
        $equipmentModel->update($data['id'], $updatedEquipment);
        return $this->respondUpdated($data['id']);
    }

    public function delete(): ResponseInterface
    {
        try {
            return $this->__delete__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    protected function __delete__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_delete');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }

        $inputParams = [
            'id'
        ];

        $inputData = $this->request->getVar($inputParams);
        $idRules = [
            'id' => [
                'rules' => [
                    'required',
                    'equipment_id_is_sanitized'
                ],
                'errors' => [
                ]
            ]
        ];
        if (!$this->validateData($inputData, $idRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        helper('equipment');
        $inputData['id'] = preprocessEquipmentId($inputData['id'] ?? '');

        $inputRules = [
            'id' => [
                'rules' => [
                    'required',
                    'is_not_unique[equipments.id]'
                ]
            ]
        ];

        if (!$this->validate($inputData, $inputRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        model('EquipmentModel')->delete($inputData['id']);
        return $this->respondDeleted($inputData['id']);
    }

    public function restore(): ResponseInterface
    {
        try {
            return $this->__restore__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    public function __restore__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_restore');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }

        $inputParams = [
            'id'
        ];

        $inputData = $this->request->getVar($inputParams);
        $idRules = [
            'id' => [
                'rules' => [
                    'required',
                    'equipment_id_is_sanitized'
                ],
                'errors' => [
                ]
            ]
        ];
        if (!$this->validateData($inputData, $idRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        helper('equipment');
        $inputData['id'] = preprocessEquipmentId($inputData['id'] ?? '');

        $inputRules = [
            'id' => [
                'rules' => [
                    'required',
                    'is_not_unique[equipments.id]'
                ]
            ]
        ];

        if (!$this->validateData($inputData, $inputRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $equipment = model('EquipmentModel')
            ->onlyDeleted()
            ->find($inputData['id']);
        if (empty($equipment)) {
            return $this->buildAPIResponse(
                200,
                [],
                'Equipment is not deleted.'
            );
        }
        model('EquipmentModel')->restore($inputData['id']);
        return $this->respondRestored($inputData['id']);
    }

    public function batchCreate(): ResponseInterface
    {
        try {
            return $this->__batchCreate__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    protected function __batchCreate__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_batch_register');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }
        $inputData = [
            'type_name' => $this->request->getVar('type_name'),
            'file' => $this->request->getFile('file')
        ];

        $notCsvMsg = "Only csv files are supported for batch registering.";
        $inputRules = [
            'type_name' => [
                "required",
                "equipment_type_name_is_sanitized",
                "is_not_unique[equipment_types.name]",
            ],
            'file' => [
                'rules' => [
                    'uploaded[file]',
                    'max_size[file,50]',
                    'mime_in[file,text/csv]',
                    'ext_in[file,csv]'
                ],
                'errors' => [
                    'uploaded' => 'You must upload a file.',
                    'max_size' => 'Max file size is 50 kB',
                    'mime_in' => $notCsvMsg,
                    'ext_in' => $notCsvMsg
                ]
            ]
        ];
        if (!$this->validateData($inputData, $inputRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        $equipValidator = EquipmentValidator::getFromTypeName($inputData['type_name']);
        // Needs to rename the fields because the front end fields
        // have user friendly names
        $res = model('EquipmentModel')->insertFromFile(
            $inputData['file']->getTempName(),
            $equipValidator
        );
        if ($res === true) {
            return $this->respondCreated();
        }
        $res['row'] = remove_value('iRow', $res);
        return $this->failValidationErrors($res, 'Invalid row.');

    }

    public function disassemble(): ResponseInterface
    {
        try {
            return $this->__disassemble__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    protected function __disassemble__(): ResponseInterface
    {
        $inputParams = [
            'parent_id',
            'child_id'
        ];

        $inputData = $this->request->getVar($inputParams);
        $idRules = [
            'parent_id' => [
                'rules' => [
                    'required',
                    'equipment_id_is_sanitized'
                ],
                'errors' => [
                ]
            ],
            'child_id' => [
                'rules' => [
                    'required',
                    'equipment_id_is_sanitized'
                ],
                'errors' => [
                ]
            ]
        ];
        if (!$this->validateData($inputData, $idRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        helper('equipment');
        $inputData['child_id'] = preprocessEquipmentId($inputData['child_id'] ?? '');
        $inputData['parent_id'] = preprocessEquipmentId($inputData['parent_id'] ?? '');

        $inputRules = [
            'child_id' => [
                'rules' => [
                    'required',
                    'is_not_unique[parent_child_equips.child_equip_id]'
                ],
                'errors' => [
                    'is_not_unique' => 'The equipment {value} ID is not assembled to any equipment.'
                ]
            ],
            'parent_id' => [
                'rules' => [
                    'required',
                    'is_not_unique[parent_child_equips.parent_equip_id]'
                ],
                'errors' => [
                    'is_not_unique' => 'The equipment {value} ID has no equipment assembled to it.'
                ]
            ],
        ];

        if ($this->validateData($inputData, $inputRules)) {
            model('EquipmentModel')->disassemble(
                $inputData['parent_id'],
                $inputData['child_id']
            );
            return $this->buildAPIResponse(200, message: 'Disassembled');
        }
        return $this->failValidationErrors($inputData['errors']);
    }

    public function assemble()
    {
        try {
            return $this->__assemble__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    protected function __assemble__()
    {
        $feature = model('FeatureModel')->findName('parent_relation');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }

        $inputParams = [
            'id',
            'slots'
        ];

        $inputData = $this->request->getVar($inputParams);
        $idRules = [
            'id' => [
                'rules' => [
                    'required',
                    'equipment_id_is_sanitized'
                ],
                'errors' => [
                ]
            ]
        ];
        if (!$this->validateData($inputData, $idRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        helper('equipment');
        $inputData['id'] = preprocessEquipmentId($inputData['id'] ?? '');

        $inputRules = [
            'id' => [
                "rules" => [
                    "is_not_unique[equipments.id]"
                ]
            ],
            'slots.*' => [
                'required',
                'is_valid_slot_for_assembly[{id}]'
            ]
        ];

        if (!$this->validateData($inputData, $inputRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        $db = db_connect();
        $equipmentModel = model('EquipmentModel');
        $typeSlotModel = model('TypeSlotModel');
        $db->transException(true)->transBegin();
        foreach ($inputData['slots.*'] as $slotInfo) {
            $slotId = $typeSlotModel
                ->select('id')
                ->where('name', $slotInfo->name)
                ->first()->id;
            $equipmentModel->updateParent(
                $slotInfo->equipment_id,
                $inputData['id'],
                $slotId
            );
        }
        $db->transCommit();
        return $this->buildAPIResponse(200, message: 'Assembled');
    }

    public function getChildren()
    {
        try {
            return $this->__getChildren__();
        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    protected function __getChildren__()
    {
        $feature = model('FeatureModel')->findName('parent_relation');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }

        $inputRules = [
            'id' => [
                "rules" => [
                    "required",
                    "equipment_id_is_sanitized",
                    "is_not_unique[equipments.id]"
                ],
                "errors" => []
            ],
        ];
        $inputData = $this->getRequestValuesFromRules($inputRules);
        if (!$this->validateData($inputData, $inputRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        $children = model('EquipmentModel')->getChildrenIDs($inputData['id']);
        return $this->buildAPIResponse(
            200,
            $children
        );
    }
}