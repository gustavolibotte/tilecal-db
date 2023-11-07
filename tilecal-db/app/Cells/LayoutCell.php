<?php

namespace App\Cells;

class LayoutCell extends BaseCell
{
    public function header(): string
    {
        helper('view');
        $equipIndexFeature = model('FeatureModel')->findName('equipment_index');
        if ($equipIndexFeature->isActive()){
            $viewData['equipmentTypes'] = model('EquipmentTypeModel')
            ->select('name, label')
            ->where('active', 1)
            ->findAll();
        } else{
            $viewData['equipmentTypes'] = [];
        }
        return default_view('cells/header', $viewData);
    }
}
