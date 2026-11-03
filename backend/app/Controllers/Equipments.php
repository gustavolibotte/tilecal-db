<?php

namespace App\Controllers;

use App\Entities\Equipment;
use App\Libraries\UpgradePosition;
use App\Libraries\PositionConflict;
use App\Validation\EquipmentRules;
use App\Validation\Validator;
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
            'id' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'id_matchMode' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'type_name' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'type_name_matchMode' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'status_name' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'status_name_matchMode' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'location_name' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'location_name_matchMode' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'owner_name' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'owner_name_matchMode' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'detector_location_name' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'detector_location_name_matchMode' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'comments' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'comments_matchMode' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'relatedFileId' => [
                '\App\Validation\GeneralRules::permitEmpty' => [],
                '\App\Validation\AppFileRules::idIsSanitized' => []
            ],
            'relatedEquipmentId' => [
                '\App\Validation\GeneralRules::permitEmpty' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => []
            ],
            'onlyDeleted' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'limit' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'offset' => ['\App\Validation\GeneralRules::permitEmpty' => []],
            'sortParams' => ['\App\Validation\GeneralRules::permitEmpty' => []]
        ];

        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];

        $equipmentModel = model('EquipmentModel')->asArray();
        
        $idFilter = $validInput['id'] ?? null;
        $idMatchMode = $validInput['id_matchMode'] ?? null;

        $sequential = null;
        if ($idFilter && strlen($idFilter) >= 14) {
            $suffix = substr($idFilter, 7);
            $cleanSuffix = preg_replace('/[^0-9]/', '', $suffix);
            if (strlen($cleanSuffix) === 7) {
                $sequential = $cleanSuffix;
            }
        }

        if ($sequential) {
            $equipmentModel->groupStart()
                           ->like('equipments.id', $idFilter)
                           ->orLike('equipments.id', $sequential)
                           ->groupEnd();
        } else {
            $equipmentModel = $this->parseDynamicFilter($idFilter, $idMatchMode, 'equipments.id', $equipmentModel);
        }
        
        $equipmentModel = $this->parseDynamicFilter($validInput['type_name'] ?? null, $validInput['type_name_matchMode'] ?? 'equals', 'equipment_types.name', $equipmentModel);
        $equipmentModel = $this->parseDynamicFilter($validInput['status_name'] ?? null, $validInput['status_name_matchMode'] ?? 'equals', 'statuses.name', $equipmentModel);
        $equipmentModel = $this->parseDynamicFilter($validInput['location_name'] ?? null, $validInput['location_name_matchMode'] ?? 'equals', 'locations.name', $equipmentModel);
        $equipmentModel = $this->parseDynamicFilter($validInput['owner_name'] ?? null, $validInput['owner_name_matchMode'] ?? 'equals', 'institutes.name', $equipmentModel);
        $equipmentModel = $this->parseDynamicFilter($validInput['detector_location_name'] ?? null, $validInput['detector_location_name_matchMode'] ?? 'equals', 'detector_locations.name', $equipmentModel);
        $equipmentModel = $this->parseDynamicFilter($validInput['comments'] ?? null, $validInput['comments_matchMode'] ?? null, 'equipments.comments', $equipmentModel);

        $rawGetParams = $this->request->getGet();
        if (is_array($rawGetParams)) {
            foreach ($rawGetParams as $key => $value) {
                if ((strpos($key, 'type_params->') === 0 || strpos($key, 'type_params_') === 0) && !str_ends_with($key, '_matchMode')) {
                    $matchModeKey = $key . '_matchMode';
                    $matchMode = $rawGetParams[$matchModeKey] ?? 'contains';
                    
                    $jsonKey = str_replace(['type_params->', 'type_params_'], '', $key);
                    
                    if (!empty($jsonKey)) {
                        $jsonCol = "JSON_UNQUOTE(JSON_EXTRACT(equipments.type_params, '$.{$jsonKey}'))";
                        $equipmentModel = $this->parseDynamicFilter($value, $matchMode, $jsonCol, $equipmentModel);
                    }
                }
            }
        }

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
            $queryModel = $equipmentModel->fullQuery();
            
            $totalRecords = $queryModel->countAllResults(false);

            if (!empty($validInput['sortParams'])) {
                $sortParams = is_string($validInput['sortParams']) ? json_decode($validInput['sortParams'], true) : $validInput['sortParams'];
                if (is_array($sortParams)) {
                    
                    $fieldMap = [
                        'id'                     => 'equipments.id',
                        'serial'                 => 'equipments.serial',
                        'comments'               => 'equipments.comments',
                        'type_name'              => 'equipment_types.name',
                        'location_name'          => 'locations.name',
                        'status_name'            => 'statuses.name',
                        'owner_mne'              => 'institutes.mne',
                        'owner_name'             => 'institutes.name',
                        'detector_location_name' => 'detector_locations.name',
                        'created_at'             => 'equipments.created_at',
                        'updated_at'             => 'equipments.updated_at',
                    ];
                    
                    foreach ($sortParams as $sort) {
                        if (isset($sort['field']) && isset($sort['order'])) {
                            $field = preg_replace('/[^a-zA-Z0-9_.\->]/', '', $sort['field']);
                            $direction = ((int)$sort['order'] === 1) ? 'ASC' : 'DESC';
                            
                            if (array_key_exists($field, $fieldMap)) {
                                $dbField = $fieldMap[$field];
                                
                                if ($direction === 'ASC') {
                                    if (in_array($field, ['created_at', 'updated_at'])) {
                                        $queryModel->orderBy("({$dbField} IS NULL)", 'ASC', false);
                                    } else {
                                        $queryModel->orderBy("({$dbField} IS NULL OR {$dbField} = '')", 'ASC', false);
                                    }
                                    $queryModel->orderBy($dbField, 'ASC', false);
                                } else {
                                    $queryModel->orderBy($dbField, 'DESC', false);
                                }
                            } 
                            else {
                                $jsonKey = str_replace(['type_params.', 'type_params->', 'type_params_'], '', $field);
                                $unquotedField = "JSON_UNQUOTE(JSON_EXTRACT(equipments.type_params, '$.{$jsonKey}'))";

                                if ($direction === 'ASC') {
                                    $queryModel->orderBy("({$unquotedField} IS NULL OR {$unquotedField} = '')", 'ASC', false);
                                    $queryModel->orderBy("({$unquotedField} + 0)", 'ASC', false);
                                    $queryModel->orderBy($unquotedField, 'ASC', false);
                                } else {
                                    $queryModel->orderBy("({$unquotedField} + 0)", 'DESC', false);
                                    $queryModel->orderBy($unquotedField, 'DESC', false);
                                }
                            }
                        }
                    }
                }
            }

            $limit = (isset($validInput['limit']) && $validInput['limit'] !== '') ? (int)$validInput['limit'] : 0;
            $offset = (isset($validInput['offset']) && $validInput['offset'] !== '') ? (int)$validInput['offset'] : 0;

            $data = $queryModel->findAll($limit, $offset);
        } else {
            $data = $equipmentModel->getRelatedEquipments($validInput['relatedEquipmentId']);
            $totalRecords = count($data);
        }
        
        helper('app_array');
        foreach ($data as &$sample) {
            $sample['type_params'] = json_decode($sample['type_params']);
        }
        
        $payload = [
            'data' => $data,
            'totalRecords' => $totalRecords
        ];

        return respondQueryResults(
            $payload,
            $inputData,
            $validInput
        );
    }

    private function createEquipmentInstance(array $inputData): string
    {
        helper(['app_array']);
        
        if (empty($inputData['id']) && !empty($inputData['serial'])) {
            $inputData['id'] = $inputData['serial'];
        }

        if (empty($inputData['type_name'])) {
            throw new \Exception("Missing equipment type.");
        }

        $registerRules = EquipmentRules::getRegisterRulesFromTypeName($inputData['type_name']);
        $validationRes = Validator::validate($inputData, $registerRules);

        if (!$validationRes['valid']) {
            throw new \Exception("Validation Error: " . json_encode($validationRes['errors']));
        }

        $validInput = $validationRes['validatedData'];

        if (!empty($validInput['owner_mne'])) {
            $inst = model('InstituteModel')->where('mne', $validInput['owner_mne'])->first();
            if($inst) $validInput['owner_id'] = $inst->id;
        } elseif (!empty($validInput['owner_name'])) {
            $inst = model('InstituteModel')->where('name', $validInput['owner_name'])->first();
            if($inst) $validInput['owner_id'] = $inst->id;
        }
        
        if (!empty($validInput['location_name'])) {
            $loc = model('LocationModel')->where('name', $validInput['location_name'])->first();
            if($loc) $validInput['location_id'] = $loc->id;
        }
        if (!empty($validInput['status_name'])) {
            $stat = model('StatusModel')->where('name', $validInput['status_name'])->first();
            if($stat) $validInput['status_id'] = $stat->id;
        }

        $equipType = model('EquipmentTypeModel')
            ->select('params')
            ->where('name', $inputData['type_name'])
            ->first();

        $validInput['type_params'] = [];
        if ($equipType && $equipType->params) {
            $paramsList = is_string($equipType->params) ? json_decode($equipType->params, true) : $equipType->params;
            foreach ($paramsList as $param) {
                $validInput['type_params'][$param] = remove_value($param, $validInput, false);
            }
        }

        if (in_array($inputData['type_name'], UpgradePosition::BLOCKS, true) && array_key_exists('box_position', $validInput)) {
            $validInput['type_params'][UpgradePosition::RESERVED] = $validInput['box_position'];
            unset($validInput['box_position']);
        }

        $equipment = new Equipment($validInput);
        $model = model('EquipmentModel');

        if ($model->insert($equipment) === false) {
            $dbErrors = $model->errors() ?? ['Unknown database error'];
            throw new \Exception("Database Insert Failed: " . implode(", ", $dbErrors));
        }

        return $equipment->id;
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
        
        $typeRules = [
            'type_name' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentTypeRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'equipment_types',
                        'column' => 'name'
                    ]
                ]
            ]
        ];

        $inputData = $this->request->getJSON(true) ?? $this->request->getVar();
        if (is_object($inputData)) {
            $inputData = (array)$inputData;
        }

        $validationRes = Validator::validate($inputData, $typeRules);
        helper(['response', 'app_array']);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }

        try {
            $id = $this->createEquipmentInstance($inputData);
            return $this->respondCreated($id);
        } catch (\Exception $e) {
            return $this->failValidationErrors(['error' => $e->getMessage()]);
        }
    }

    public function batchValidate(): ResponseInterface
    {
        ini_set('memory_limit', '512M');
        try {
            return $this->__batchValidate__();
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'messages' => [
                    'error' => "CRITICAL PHP ERROR: " . $e->getMessage() . " | File: " . basename($e->getFile()) . " | Line: " . $e->getLine()
                ]
            ]);
        }
    }

    protected function __batchValidate__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_batch_register');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }

        $inputData = $this->request->getJSON(true);
        if (!isset($inputData['equipments']) || !is_array($inputData['equipments'])) {
            return $this->failValidationErrors(['equipments' => 'Invalid payload format. Expected an array of equipments.']);
        }

        $results = [];
        $hasErrors = false;
        
        foreach ($inputData['equipments'] as &$row) {
            if (empty($row['id']) && !empty($row['serial'])) {
                $row['id'] = $row['serial'];
            }
        }

        unset($row);
        
        $incomingIds = array_filter(array_column($inputData['equipments'], 'id'));
        $existingIds = [];
        
        if (!empty($incomingIds)) {
            $existingEquips = model('EquipmentModel')->select('id')->whereIn('id', $incomingIds)->findAll();
            foreach ($existingEquips as $e) {
                $existingIds[] = is_array($e) ? $e['id'] : $e->id;
            }
        }

        $seenIdsInBatch = [];

        foreach ($inputData['equipments'] as $index => $row) {
            $rowStatus = [
                'rowIndex' => $index,
                'valid' => true,
                'errors' => []
            ];

            $idToUse = $row['id'] ?? null;

            if (!$idToUse) {
                 $rowStatus['valid'] = false;
                 $rowStatus['errors']['id'] = "Missing ID (or Serial for auto-mapping).";
            } else {
                 if (in_array($idToUse, $existingIds)) {
                     $rowStatus['valid'] = false;
                     $rowStatus['errors']['id'] = "ID '{$idToUse}' already exists in the database.";
                 }
                 if (in_array($idToUse, $seenIdsInBatch)) {
                     $rowStatus['valid'] = false;
                     $rowStatus['errors']['id'] = "ID '{$idToUse}' is duplicated within this batch.";
                 }
                 $seenIdsInBatch[] = $idToUse;
            }

            if (!empty($row['type_name'])) {
                $registerRules = EquipmentRules::getRegisterRulesFromTypeName($row['type_name']);
                $validationRes = Validator::validate($row, $registerRules);
                if (!$validationRes['valid']) {
                    $rowStatus['valid'] = false;
                    $rowStatus['errors'] = array_merge($rowStatus['errors'], $validationRes['errors']);
                }
            } else {
                 $rowStatus['valid'] = false;
                 $rowStatus['errors']['type_name'] = "Missing equipment type.";
            }

            if (!$rowStatus['valid']) {
                $hasErrors = true;
            }
            
            $results[] = $rowStatus;
        }

        return $this->response->setJSON([
            'data' => [
                'valid' => !$hasErrors,
                'details' => $results
            ]
        ]);
    }

    public function batchCreate(): ResponseInterface
    {
        ini_set('memory_limit', '512M');
        try {
            return $this->__batchCreate__();
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'messages' => [
                    'error' => "CRITICAL DB ERROR: " . $e->getMessage() . " | File: " . basename($e->getFile()) . " | Line: " . $e->getLine()
                ]
            ]);
        }
    }

    protected function __batchCreate__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_batch_register');
        if (!$feature->isActive()) {
            return $this->failServiceUnavailable();
        }
        
        $inputData = $this->request->getJSON(true);
        if (!isset($inputData['equipments']) || !is_array($inputData['equipments'])) {
            return $this->failValidationErrors(['equipments' => 'Invalid payload format. Expected JSON array.']);
        }

        $db = db_connect();
        $db->transException(true)->transBegin();
        
        try {
            foreach ($inputData['equipments'] as $iRow => $row) {
                $this->createEquipmentInstance($row);
            }
            
            $db->transCommit();
            return $this->respondCreated();
            
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 400,
                'error' => 400,
                'messages' => [
                    'error' => "Batch process failed at row " . ($iRow ?? 0) . ". Reason: " . $e->getMessage()
                ]
            ]); 
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
        helper('response');
        $inputRules = [
            'id' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
            ]
        ];
        $inputData = $this->request->getVar(array_keys($inputRules));
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

    // PMT positions integration v1
    public function put(): ResponseInterface
    {
        $db = db_connect();
        try {
            $db->transException(true)->transBegin();
            $response = $this->__put__();
            if ($response->getStatusCode() >= 400) {
                $db->transRollback();
            } else {
                if (!$db->transStatus() || !$db->transCommit()) {
                    throw new \RuntimeException('Could not commit equipment update.');
                }
            }
            return $response;
        } catch (Throwable $e) {
            $db->transRollback();
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
        helper(['response', 'app_array']);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $id = $validationRes['validatedData']['id'];
        // A locking read must also supply the JSON; a later snapshot read can be stale.
        $locked = db_connect()->query('SELECT e.type_params, t.name AS type_name, t.params
            FROM equipments e JOIN equipment_types t ON t.id=e.type_id
            WHERE e.id=? AND e.deleted_at IS NULL FOR UPDATE', [$id])->getRowArray();
        if (!$locked) return $this->failNotFound();
        $queryData = new Equipment($locked);
        $submittedPositions = $this->request->getJSON(true);
        if (is_array($submittedPositions) && ($queryData->type_name === 'PMT' || in_array($queryData->type_name, UpgradePosition::BLOCKS, true))
            && (array_key_exists('box_position', $submittedPositions) || array_key_exists('phase1_position', $submittedPositions)
                || array_key_exists(UpgradePosition::RESERVED, $submittedPositions))) {
            return $this->failBadRequest(null, 'Use the position editor to update positions.');
        }
        $equipRules = EquipmentRules::getEditRulesFromTypeName($queryData->type_name);
        $inputData = $this->request->getVar(array_keys($equipRules));
        $clearedFields = [];
        if (in_array($queryData->type_name, ['PMT Block', 'PMT Block (Standard)', 'PMT Block (Long Cable)'], true)) {
            $submitted = $this->request->getJSON(true);
            if (is_array($submitted)) {
                foreach (['detector_location_name', 'serial', 'comments'] as $field) {
                    if (array_key_exists($field, $submitted) && ($submitted[$field] === null || $submitted[$field] === '')) {
                        $clearedFields[$field === 'detector_location_name' ? 'detector_location_id' : $field] = null;
                    }
                }
            }
        }
        $validationRes = Validator::validate($inputData, $equipRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        $validInput = array_merge($validInput, $clearedFields);
        $validInput['type_params'] = $queryData->type_params;
        foreach(json_decode($queryData->params, true) as $param) {
            if (array_key_exists($param, $validInput)) {
                $validInput['type_params'][$param] = remove_value($param, $validInput);
            }
        }
        $equip = new Equipment($validInput);
        model('EquipmentModel')->update($equip->id, $equip);

        if (isset($validInput['location_name'])) {
            model('EquipmentModel')->updateLocationCascade($equip->id, $validInput['location_name']);
        }

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
        $db = db_connect();
        try {
            $db->query('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $db->transException(true)->transBegin();
            $response = $this->__assemble__();
            if ($response->getStatusCode() >= 400) $db->transRollback();
            elseif (!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Could not commit assembly.');
            return $response;
        } catch (PositionConflict $e) {
            $db->transRollback();
            return $this->buildAPIResponse(409, $e->details, $e->getMessage());
        } catch (\DomainException $e) {
            $db->transRollback();
            return $this->buildAPIResponse(409, null, $e->getMessage());
        } catch (Throwable $e) {
            $db->transRollback();
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
        $inputData = std_to_array($inputData);
        $request = $this->request->getJSON(true);
        $resolution = $request['upgrade_resolution'] ?? null;
        if ($resolution !== null && !is_array($resolution)) return $this->failBadRequest();
        // Lock the participants before checking slots; concurrent assemblies must revalidate.
        if (!is_array($inputData['slots'] ?? null)) return $this->failBadRequest();
        $ids = [$inputData['id'] ?? null];
        $names = []; $children = [];
        foreach ($inputData['slots'] as $slot) {
            if (!is_array($slot) || !is_string($slot['name'] ?? null)) return $this->failBadRequest();
            $names[] = $slot['name']; $children[] = $slot['equipment_id'] ?? null;
            $ids[] = $slot['equipment_id'] ?? null;
        }
        if (count(array_unique($names)) !== count($names)) return $this->failBadRequest(null, 'Duplicate assembly slot.');
        foreach ($ids as $candidate) {
            if (!is_string($candidate) || !preg_match('/^[A-Z0-9]{7}$/D', $candidate)) return $this->failBadRequest();
        }
        if (in_array($inputData['id'], $children, true)) return $this->failBadRequest(null, 'Equipment cannot be mounted into itself.');
        if (count(array_unique($children)) !== count($children)) return $this->failBadRequest(null, 'Duplicate component.');
        $ids = array_unique($ids); sort($ids);
        foreach ($ids as $candidate) UpgradePosition::equipment($candidate);

        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];

        $db = db_connect();
        $equipmentModel = model('EquipmentModel');
        $typeSlotModel = model('TypeSlotModel');
        // Resolve every position before inserting any slots. Conflict tokens must describe
        // committed state, not relation IDs that would be rolled back on a conflict.
        $positionPlans = [];
        foreach ($validInput['slots'] as $slotInfo) {
            $positionPlans[$slotInfo['equipment_id']] = UpgradePosition::prepareDrawer($validInput['id'], $slotInfo)
                ?? UpgradePosition::prepareAssembly(
                $validInput['id'], $slotInfo['equipment_id'], $resolution);
        }
        foreach ($validInput['slots'] as $slotInfo) {
            $slotId = $typeSlotModel
                ->select('id')
                ->where('name', $slotInfo['name'])
                ->first()->id;
            $equipmentModel->updateParent(
                $slotInfo['equipment_id'],
                $validInput['id'],
                $slotId,
                $resolution,
                $positionPlans[$slotInfo['equipment_id']]
            );
        }
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

    public function relateFile()
    {
        helper('response');
        try {
            return $this->__relateFile__();
        } catch (Throwable $e) {
            return failInternalError($e);
        }
    }

    public function __relateFile__()
    {
        $feature = model('FeatureModel')->findName('file_equip_relation');
        if (!$feature->isActive()) {
            return failServiceUnavailable();
        }

        $inputRules = config('Validation')->relateUnrelateFileEquipRules;
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        $fileId = model('FileModel')
            ->select('id')
            ->where('name', $validInput['file_name'])
            ->first()->id;
        model('Equipment2FileModel')->insert([
            'equipment_id' => $validInput['equipment_id'],
            'file_id' => $fileId
        ]);
        helper('response');
        return buildAPIResponse(
            200,
            message: 'Related'
        );
    }

    public function unrelateFile()
    {
        helper('response');
        try {
            return $this->__unrelateFile__();
        } catch (Throwable $e) {
            return failInternalError($e);
        }
    }

    public function __unrelateFile__()
    {
        $feature = model('FeatureModel')->findName('file_equip_relation');
        if (!$feature->isActive()) {
            return failServiceUnavailable();
        }

        $inputRules = config('Validation')->relateUnrelateFileEquipRules;
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return failValidationErrors($validationRes['errors'], $inputData);
        }
        $validInput = $validationRes['validatedData'];
        $fileId = model('FileModel')
            ->select('id')
            ->where('name', $validInput['file_name'])
            ->first()->id;
        $relationId = model('Equipment2FileModel')
            ->where('equipment_id', $validInput['equipment_id'])
            ->where('file_id', $fileId)
            ->first()->id;
        model('Equipment2FileModel')->delete($relationId, purge: true); 
        helper('response');
        return buildAPIResponse(
            200,
            message: 'Unrelated'
        );
    }

    public function getInconsistencies(): ResponseInterface
    {
        try {
            $feature = model('FeatureModel')->findName('api_equipment_get');
            if (!$feature->isActive()) {
                return $this->failServiceUnavailable();
            }
            
            $data = model('EquipmentModel')->getInconsistentAssemblies('PMT Block');
            
            return $this->buildAPIResponse(200, $data);
            
        } catch (\Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    public function batchUpdateLocations(): ResponseInterface
    {
        try {
            $feature = model('FeatureModel')->findName('equipment_edit');
            if (!$feature->isActive()) {
                return $this->failServiceUnavailable();
            }

            $inputData = $this->request->getJSON(true);
            if (!isset($inputData['updates']) || !is_array($inputData['updates'])) {
                return $this->failValidationErrors(['updates' => 'Invalid payload format. Expected an array of updates.']);
            }

            $db = db_connect();
            $db->transException(true)->transBegin();

            $equipModel = model('EquipmentModel');
            
            foreach ($inputData['updates'] as $update) {
                if (!isset($update['id']) || !isset($update['location_name'])) continue;
                
                $equip = new Equipment([
                    'id' => $update['id'],
                    'location_name' => $update['location_name']
                ]);
                $equipModel->update($update['id'], $equip);
            }

            $db->transCommit();
            return $this->respondUpdated();
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'messages' => ['error' => "CRITICAL DB ERROR: " . $e->getMessage()]
            ]);
        }
    }
}