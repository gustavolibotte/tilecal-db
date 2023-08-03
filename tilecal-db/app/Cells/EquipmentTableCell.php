<?php

namespace App\Cells;

class EquipmentTableCell
{
    public function side(int $equipmentId=null): string
    {
        $equipmentModel = new \App\Models\EquipmentModel();
        if (is_null($equipmentId)){
           $viewData['equipments'] = $equipmentModel->getEquipment(limit: 30);
        } else{
           $viewData['equipments'] = $equipmentModel->getRelatedEquipments($equipmentId);
        }
        return view('cells/equipment_table_side', $viewData);
    }

    public function sidejs()
    {
        $jsUrl = base_url('assets/js/cells/equipment_table_side.js');
        $script = sprintf('<script type="text/javascript" src="%s" ></script>', $jsUrl);
        return $script;
    }
}
