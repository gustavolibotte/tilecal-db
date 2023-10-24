<?php

namespace App\Cells;

class EquipmentTableCell extends BaseCell
{
    public function side(array $params=[])
    {   
        helper(['array', 'view']);
        $equipmentId = dot_array_search('equipmentId', $params);
        $equipmentModel = new \App\Models\EquipmentModel();
        if (is_null($equipmentId)){
           $viewData['equipments'] = $equipmentModel->fullQuery()
                                                    ->orderBy('updated_at', 'DESC')
                                                    ->findAll(limit: 30);
        } else{
           $viewData['equipments'] = $equipmentModel->getRelatedEquipments($equipmentId);
        }
        return default_view('cells/equipment_table_side', $viewData);
    }

    public function sidejs()
    {
        return $this->getJs('equipment_table_side.js');
    }
}
