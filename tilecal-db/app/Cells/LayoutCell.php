<?php

namespace App\Cells;

class LayoutCell extends BaseCell
{
    public function header(): string
    {
        helper('view');
        $viewData['equipmentTypes'] = model('EquipmentTypeModel')
            ->select('name, label')
            ->findAll();
        return default_view('cells/header', $viewData);
    }
}
