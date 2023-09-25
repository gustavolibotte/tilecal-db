<?php

namespace App\Database\Seeds;

use App\Entities\Equipment;
use App\Library\DataFrame;
use CodeIgniter\Entity\Entity;

class CSVSeeder extends BaseSeeder
{
    public function run()
    {
        helper('app_array');
        $csvFilepath = '/usr/local/cernbox/backups/old_database/2023_07_17/2023_09_20_13_02_52_cleaned_pmt_202307171723.csv';
        $equipmentTypeName = 'pmt';
        $colsRename = [
            'type' => 'type_name',
            'owner' => 'owner_mne',
            'location' => 'location_name',
            'status' => 'status_name'
        ];
        $dataFrame = DataFrame::fromCSV($csvFilepath, $colsRename);
        $equipmentType = model('EquipmentTypeModel')->where('name', $equipmentTypeName)->first();
        $equipmentModel = model('EquipmentModel');
        foreach ($dataFrame->data as $entityData){
            if ($entityData['qe'] == ''){
                $entityData['qe'] = null;
            }
            if ($entityData['qe'] == ''){
                $entityData['qe'] = null;
            }
            $entityData['type_params'] = $this->buildTypeParams(
                $entityData,
                $equipmentType
            );
            $equipment = new Equipment($entityData);
            $equipmentModel->insert($equipment);
        }
    }

    protected function buildTypeParams(array $entityData, Entity $equipmentType)
    {
        $typeParams = array();
        foreach ($equipmentType->params as $paramName){
            $typeParams[$paramName] = dot_search_with_default($paramName, $entityData, null);
        }
        return $typeParams;
    }
}
