<?php

namespace App\Cells;

class LayoutCell extends BaseCell
{
    public function header(): string
    {
        helper(['view', 'string']);
        $featureNames = [
            'equipment_index',
            'equipment_delete',
            'equipment_register',
            'equipment_batch_register',
            'file_upload',
            'file_index',
            'file_delete',
            'help'
        ];
        $features = model('FeatureModel')
            ->whereIn('name', $featureNames)
            ->findAll();
        foreach($features as $feature){
            $viewData[sprintf("%sActive", snake2LowerCamelCase($feature->name))] = 
                $feature->isActive();
        }
        if ($viewData['equipmentIndexActive'] || $viewData['equipmentDeleteActive']){
            $viewData['equipmentTypes'] = model('EquipmentTypeModel')
                ->select('name')
                ->where('active', 1)
                ->findAll();
        } else{
            $viewData['equipmentTypes'] = [];
        }
        return default_view('cells/header', $viewData);
    }
}
