<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class EquipmentType extends APIController
{
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
        $inputRules = [
            'type_name' => [
                'rules' => [
                    'permit_empty',
                    'equipment_type_name_is_sanitized'
                ],
                'errors' => []
            ]
        ];
        $res = $this->getValidatedData($inputRules);
        if (!$res['valid']){
            return $this->failInvalidParams($res['errors']);
        }
        $inputData = $res['data'];

        $equipmentTypeModel = model('EquipmentTypeModel');
        $this->parseFilterCondition($inputData['type_name'], 'equipment_types.name', $equipmentTypeModel);
        $data = $equipmentTypeModel
            ->joinWithGroupedTypeSlots()
            ->select('name')
            ->select('params')
            ->select('slot_names')
            ->where('active', 1)
            ->findAll();
        return $this->respondQueryResults($data);
    }

}
