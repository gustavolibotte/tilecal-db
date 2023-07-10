<?php

namespace App\Controllers;

use App\Models\EquipmentModel;
use App\Models\ModelModel;

class PMT extends Equipments
{   
    public function getRegisterData($validationErrors=null, $preFilledValues=null){

        $registerData['enableFileLink'] = false;
        $equipmentModel = new EquipmentModel();
        $registerData['relatedEquipments'] = $equipmentModel->getSideTable();
        $modelModel = new ModelModel();
        $registerData['model'] = $modelModel->where("name = 'pmt'")->first();
        $registerData['validationErrors'] = $validationErrors;
        $registerData['formPlaceholders'] = [
            'id' => 'Equipment barcode number',
            'location' => 'Equipment location',
            'status' => 'Installed, assembled, broken, etc.',
            'comments' => 'Useful info about the equipment',
            'beta' => 'PMT beta parameter value',
            'nominal_hv' => 'PMT nominal HV value',
            'qe' => 'PMT QE value',
            'type' => 'PMT Type'
        ];
        $registerData['formRestrictions'] = [
        ];
        // $registerData['maxDescriptionLength'] = DBFileRules::$maxDescriptionSize;
        if (is_null($preFilledValues)){
            $registerData['preFilledValues'] = [
                'id' => null,
                'location' => null,
                'status' => null,
                'comments' => null,
                'beta' => null,
                'nominal_hv' => null,
                'qe' => null,
                'type' => null
            ];
        }
        else{
            $registerData['preFilledValues'] = $preFilledValues;
        }

        return $registerData;
    }
    public function registerGet()
    {
        helper('form');

        $headerData = $this->getHeaderData(pageTitle: 'PMT Register');
        $registerData = $this->getRegisterData();

        return view('templates/header', $headerData)
              .view('equipment/pmt/register', $registerData)
              .view('templates/footer');
    }

    public function registerPost(){

        helper('form');

        $formInputs = $this->request->getPost([
            'id',
            'location',
            'status',
            'comments',
            'beta',
            'nominal_hv',
            'qe',
            'type'
        ]);
        $formInputs['model'] = "pmt";
        $formInputs['location'] = $this->toSnakeCase($formInputs['location']);
        $formInputs['status'] = $this->toSnakeCase($formInputs['status']);
        $formInputs['type'] = $this->toSnakeCase($formInputs['type']);

        if ($this->validateData($formInputs, 'pmtRegisterRules')){
            $registerData = $this->getRegisterData();
            $equipmentModel = new EquipmentModel();
            $equipmentModel -> insert($formInputs);
        }
        else{
            $validationErrors = $this->validator->getErrors();
            $registerData = $this->getRegisterData(
                $validationErrors,
                $formInputs
            );
        }

        $headerData = $this->getHeaderData(pageTitle: 'PMT Register');

        return view('templates/header', $headerData)
              .view('equipment/pmt/register', $registerData)
              .view('templates/footer');
    }
}
