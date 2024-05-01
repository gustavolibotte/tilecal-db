<?php

namespace App\Controllers;

use App\Entities\Equipment;
use App\Validation\EquipmentRules;
use App\Validation\Validator;
use App\Library\Readers\CSVFileReader;
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
        helper('response');
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
            return failValidationErrors($validationRes['errors'], $inputData);
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
        return respondQueryResults(
            $data,
            $inputData,
            $validInput
        );
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
        helper('response');
        $inputData = $this->request->getVar(['type_name']);
        $registerRules = EquipmentRules::getRegisterRulesFromTypeName($inputData['type_name']);
        $inputData = $this->request->getVar(array_keys($registerRules));
        $validationRes = Validator::validate($inputData, $registerRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }

        $validInput = $validationRes['validatedData'];
        $equipment = new Equipment($validInput);
        model('EquipmentModel')->insert($equipment);
        return $this->respondCreated($equipment->id);
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
        helper('response');
        $inputRules = [
            'id' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
            ]
        ];
        $inputData = $this->getRequestValuesFromRules($inputRules);
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        $history = model('HistModel')
            ->getEquipmentHist($validInput['id']);
        helper('response');
        return respondQueryResults(
            $history,
            $inputData,
            $validInput
        );
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

        $idRules = [
            'id' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'id'
                    ]
                ]
            ]
        ];
        $inputData = $this->request->getVar(array_keys($idRules));
        $validationRes = Validator::validate($inputData, $idRules);
        helper('response');
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $id = $validationRes['validatedData']['id'];

        $equipRules = EquipmentRules::getEditRulesFromId($id);
        $inputData = $this->request->getVar(array_keys($equipRules));
        echo "Validating full input data\n";
        $validationRes = Validator::validate($inputData, $equipRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        $id = $validInput['id'];
        unset($validInput['id']);
        model('EquipmentModel')->update($id, $validInput);

        return respondUpdated($id, $inputData, $validInput);
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

        $inputRules = [
            'id' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotDeleted' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'id'
                    ]
                ]
            ]
        ];
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return $this->failValidationErrors($validationRes['errors']);
        }
        $validInput = $validationRes['validatedData'];
        model('EquipmentModel')->delete($validInput['id']);
        return $this->respondDeleted($validInput['id']);
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
        helper('response');
        $feature = model('FeatureModel')->findName('equipment_restore');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }

        $inputRules = [
            'id' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isDeleted' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'id'
                    ]
                ]
            ]
        ];
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return $this->failValidationErrors($validationRes['errors']);
        }
        $validInput = $validationRes['validatedData'];

        $equipment = model('EquipmentModel')
            ->onlyDeleted()
            ->find($validInput['id']);
        if (empty($equipment)) {
            return $this->buildAPIResponse(
                200,
                [],
                'Equipment is not deleted.'
            );
        }
        model('EquipmentModel')->restore($validInput['id']);
        return $this->respondRestored($validInput['id']);
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
        $inputRules = [
            'file' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\FileRules::maxSize' => [
                    'maxSize' => 50 * 1024  // 50 kB
                ],
                '\App\Validation\FileRules::mimeIn' => [
                    'mimeTypes' =>['text/csv']
                ],
                '\App\Validation\FileRules::extIn' => [
                    'exts' => ['csv']
                ]
            ]
        ];
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return $this->failValidationErrors($validationRes['errors']);
        }
        $validInput = $validationRes['validatedData'];

        $inputFile = new CSVFileReader($validInput->getTempName());
        $db = db_connect();
        $db->transException(true)->transBegin();
        foreach ($inputFile as $iRow=>$row){
            $registerRules = EquipmentRules::getRegisterRulesFromTypeName($row['type_name']);
            $validationRes = Validator::validate($inputData, $registerRules);
            if (!$validationRes['valid']) {
                $errors = $validationRes['errors'];
                $errors['row_number'] = $iRow;
                return $this->failValidationErrors($errors);
            }
            $validInput = $validationRes['validatedData'];
            $equipment = new Equipment($validInput);
            model('EquipmentModel')->insert($equipment);
        }
        $db->transCommit();
        return $this->respondCreated();
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
        $inputRules = [
            'parent_id' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'id'
                    ]
                ]
            ],
            'child_id' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'id'
                    ]
                ]
            ]
        ];

        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return $this->failValidationErrors($validationRes['errors']);
        }
        $validInput = $validationRes['validatedData'];
        model('EquipmentModel')->disassemble(
            $validInput['parent_id'],
            $validInput['child_id']
        );
        return $this->buildAPIResponse(200, message: 'Disassembled');
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

        $inputRules = [
            'id' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'id'
                    ]
                ]
            ],
            'slots' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\GeneralRules::isArray' => [],
                '\App\Validation\EquipmentRules::validAssemblySlots' => [
                    'params' => [
                        'parent_id_field' => 'id'
                    ]
                ]
            ]
        ];

        $inputData = $this->request->getVar(array_keys($inputRules));
        helper(['cast', 'response']);
        $inputData = std_to_array($inputData);  // The data inside slots come as an stdClass
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];

        $db = db_connect();
        $equipmentModel = model('EquipmentModel');
        $typeSlotModel = model('TypeSlotModel');
        $db->transException(true)->transBegin();
        foreach ($validInput['slots'] as $slotInfo) {
            $slotId = $typeSlotModel
                ->select('id')
                ->where('name', $slotInfo['name'])
                ->first()->id;
            $equipmentModel->updateParent(
                $slotInfo['equipment_id'],
                $validInput['id'],
                $slotId
            );
        }
        $db->transCommit();
        return buildAPIResponse(200, message: 'Assembled');
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
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'id'
                    ]
                ]
            ]
        ];
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return $this->failValidationErrors($validationRes['errors']);
        }
        $validInput = $validationRes['validatedData'];

        $children = model('EquipmentModel')->getChildrenIDs($validInput['id']);
        return $this->buildAPIResponse(
            200,
            $children
        );
    }
}