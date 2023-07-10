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
}
