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

    public function full(): string
    {
        return default_view('cells/equipment_table_full');
    }

    public function fulljs(): string
    {
        return $this->getJs('equipment_table_full.js');
    }

    public function fullEquipmentRelated(array $params): string
    {
        helper(['app_array', 'view', 'string']);
        $equipmentId = dot_array_search('equipmentId', $params);
        $equipmentModel = new \App\Models\EquipmentModel();
        if(dot_search_with_default('withDeleted', $params, false)){
            $equipmentModel->withDeleted();
        }
        $viewData['equipments'] = $equipmentModel->getRelatedEquipments($equipmentId);
        return default_view('cells/equipment_table_related', $viewData);
    }

    public function fullEquipmentRelatedjs(): string
    {
        return $this->getJs('equipment_table_related.js');
    }

    public function fullFileRelated(array $params): string
    {
        helper(['app_array', 'view']);
        $fileId = dot_search_with_default('fileId', $params);
        $equipmentModel = new \App\Models\EquipmentModel();
        if(dot_search_with_default('withDeleted', $params, false)){
            $equipmentModel->withDeleted();
        }
        $viewData['equipments'] = $equipmentModel->getFileRelated($fileId);
        return default_view('cells/equipment_table_full', $viewData);
    }

    public function fullFileRelatedjs(): string
    {
        return $this->getJs('equipment_table_full.js');
    }
}
