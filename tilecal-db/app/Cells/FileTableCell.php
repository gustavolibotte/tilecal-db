<?php

namespace App\Cells;

class FileTableCell extends BaseCell
{
    public function side()
    {
        $fileModel = new \App\Models\FileModel();
        $viewData['files'] = $fileModel->findAll();
        return default_view('cells/file_table_side', $viewData);
    }

    public function sidejs()
    {
        return $this->getJs('file_table_side.js');
    }
}
