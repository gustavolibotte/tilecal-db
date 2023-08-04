<?php

namespace App\Cells;

class EquipmentTableCell extends BaseCell
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
        return $this->getJs('equipment_table_side.js');
    }

    public function sideFileRelated($fileId)
    {
        $equipmentModel = new \App\Models\EquipmentModel();
        $viewData['equipments'] = $equipmentModel->getFileRelated($fileId);
        return view('cells/equipment_table_side', $viewData);
    }

    public function sideFileRelatedjs()
    {
        return $this->getJs('equipment_table_side.js');
    }
}
