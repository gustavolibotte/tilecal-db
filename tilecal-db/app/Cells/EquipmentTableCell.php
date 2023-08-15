<?php

namespace App\Cells;

class EquipmentTableCell extends BaseCell
{
    public function side(array $params=[])
    {   
        helper('array');
        $equipmentId = dot_array_search('equipmentId', $params);
        $equipmentModel = new \App\Models\EquipmentModel();
        if (is_null($equipmentId)){
           $viewData['equipments'] = $equipmentModel->getEquipment(limit: 30);
        } else{
           $viewData['equipments'] = $equipmentModel->getRelatedEquipments($equipmentId);
        }
        return default_view('cells/equipment_table_side', $viewData);
    }

    public function sidejs()
    {
        return $this->getJs('equipment_table_side.js');
    }

    public function sideFileRelated(array $params)
    {
        helper('array');
        $fileId = dot_array_search('fileId', $params);
        $equipmentModel = new \App\Models\EquipmentModel();
        $viewData['equipments'] = $equipmentModel->getFileRelated($fileId, limit:30);
        return default_view('cells/equipment_table_side', $viewData);
    }

    public function sideFileRelatedjs()
    {
        return $this->getJs('equipment_table_side.js');
    }

    public function full(): string
    {
        $equipmentModel = new \App\Models\EquipmentModel();
        $viewData['equipments'] = $equipmentModel->getEquipment();
        return default_view('cells/equipment_table_full', $viewData);
    }

    public function fulljs(): string
    {
        return $this->getJs('equipment_table_full.js');
    }

    public function fullDeleted(): string
    {
        $equipmentModel = new \App\Models\EquipmentModel();
        $viewData['equipments'] = $equipmentModel->onlyDeleted()->getEquipment();
        return default_view('cells/equipment_table_full_deleted', $viewData);
    }

    public function fullDeletedjs(): string
    {
        return $this->getJs('equipment_table_full_deleted.js');
    }

    public function fullEquipmentRelated(array $params): string
    {
        helper('array');
        $equipmentId = dot_array_search('equipmentId', $params);
        $withDeleted = dot_array_search('withDeleted', $params);
        $equipmentModel = new \App\Models\EquipmentModel();
        if ($withDeleted){
            $equipmentModel->withDeleted();
        }
        $viewData['equipments'] = $equipmentModel->getRelatedEquipments($equipmentId);
        return default_view('cells/equipment_table_full', $viewData);
    }

    public function fullEquipmentRelatedjs(): string
    {
        return $this->getJs('equipment_table_full.js');
    }

    public function fullFileRelated(array $params): string
    {
        helper('array');
        $fileId = dot_array_search('fileId', $params);
        $equipmentModel = new \App\Models\EquipmentModel();
        $viewData['equipments'] = $equipmentModel->getFileRelated($fileId);
        return default_view('cells/equipment_table_full', $viewData);
    }

    public function fullFileRelatedjs(): string
    {
        return $this->getJs('equipment_table_full.js');
    }
}
