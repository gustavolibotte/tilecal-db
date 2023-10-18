<?php

namespace App\Cells;

class LayoutCell extends BaseCell
{
    public function header(): string
    {
        helper('view');
        $viewData['equipmentTypes'] = model('EquipmentTypeModel')
            ->select('name, label')
            ->where('active', 1)
            ->findAll();
        return default_view('cells/header', $viewData);
    }
}
