<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;

class Files extends BaseController
{
    public function index()
    {
        $fileModel = new FileModel();
        $indexData['files'] = $fileModel
            ->select('id, name, comments')
            ->orderBy('last_update', 'DESC')
            ->findAll();
        
        $headerData['pageTitle'] = 'File Index';

        return view('templates/header', $headerData)
              .view('files/index', $indexData)
              .view('templates/footer');
    }
}
