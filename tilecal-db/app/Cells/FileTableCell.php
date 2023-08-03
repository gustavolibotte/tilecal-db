<?php

namespace App\Cells;

class FileTableCell
{
    public function all()
    {
        $fileModel = new \App\Models\FileModel();
        $viewData['files'] = $fileModel->findAll();
        return view('cells/file_index', $viewData);
    }

    public function equipmentRelated($params): string
    {
        $fileModel = new \App\Models\FileModel();
        $viewData['files'] = $fileModel->getEquipmentRelated($params['equipmentId']);
        return view('cells/file_table', $viewData);
    }

    public function equipmentRelatedjs()
    {
        $jsUrl = base_url('assets/js/cells/file_table_equipment_related.js');
        $script = sprintf('<script type="text/javascript" src="%s" ></script>', $jsUrl);
        return $script;
    }
}
