<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;
use App\Validation\Validator;

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
                '\App\Validation\GeneralRules::permitEmpty' => [],
                '\App\Validation\EquipmentTypeRules::nameIsSanitized' => [],
            ]
        ];
        $inputData = $this->request->getVar(array_keys($inputRules));
        $validationRes = Validator::validate($inputData, $inputRules);
        if (!$validationRes['valid']) {
            return $this->failValidationErrors($validationRes['errors']);
        }
        $validInput = $validationRes['validatedData'];

        $equipmentTypeModel = model('EquipmentTypeModel');
        $this->parseFilterCondition($validInput['type_name'], 'equipment_types.name', $equipmentTypeModel);
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
