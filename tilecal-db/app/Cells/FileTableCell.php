<?php

namespace App\Cells;

class FileTableCell extends BaseCell
{
    public function full()
    {
        $fileModel = new \App\Models\FileModel();
        $viewData['files'] = $fileModel->findAll();
        return view('cells/file_table_full', $viewData);
    }

    public function fulljs()
    {
        return $this->getJs('file_table_full.js');
    }

    public function fullEquipmentRelated(array $params): string
    {
        helper('array');
        $equipmentId = dot_array_search('equipmentId', $params);
        $withDeleted = dot_array_search('withDeleted', $params);
        $fileModel = new \App\Models\FileModel();
        if ($withDeleted){
            $fileModel = $fileModel->withDeleted();
        }
        $viewData['files'] = $fileModel->getEquipmentRelated($equipmentId);
        return view('cells/file_table_full', $viewData);
    }

    public function fullEquipmentRelatedjs(): string
    {
        return $this->getJs('file_table_full.js');
    }

    public function side()
    {
        $fileModel = new \App\Models\FileModel();
        $viewData['files'] = $fileModel->findAll();
        return view('cells/file_table_side', $viewData);
    }

    public function sidejs()
    {
        return $this->getJs('file_table_side.js');
    }
}
