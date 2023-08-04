<?php

namespace App\Cells;

class FileTableCell extends BaseCell
{
    public function full($params=[])
    {
        helper('array');
        $equipmentId = dot_array_search('equipmentId', $params);
        $fileModel = new \App\Models\FileModel();
        if (is_null($equipmentId)){
            $viewData['files'] = $fileModel->findAll();
        } else{
            $viewData['files'] = $fileModel->getEquipmentRelated($equipmentId);
        }
        return view('cells/file_table_full', $viewData);
    }

    public function side()
    {
        $fileModel = new \App\Models\FileModel();
        $viewData['files'] = $fileModel->findAll();
        return view('cells/file_table_side', $viewData);
    }

    public function fulljs()
    {
        return $this->getJs('file_table_full.js');
    }

    public function sidejs()
    {
        return $this->getJs('file_table_side.js');
    }
}
