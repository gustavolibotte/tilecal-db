<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use App\Models\EquipmentModel;

class Files extends BaseController
{
    public function index()
    {
        $fileModel = new FileModel();
        $indexData['files'] = $fileModel->getFile();
        
        $headerData['pageTitle'] = 'File Index';

        return view('templates/header', $headerData)
              .view('files/index', $indexData)
              .view('templates/footer');
    }

    public function view($id){
        $equipmentModel = new EquipmentModel();
        $viewData['equipments'] = $equipmentModel->getEquipment();

        $fileModel = new FileModel();
        $viewData['fileInfo'] = $fileModel->getFile($id);


        $headerData['pageTitle'] = sprintf('File %s', $id);

        return view('templates/header', $headerData)
              .view('files/view', $viewData)
              .view('templates/footer');
    }

    public function registerGet(){
        helper('form');

        $headerData = $this->getHeaderData(pageTitle: 'File Upload');
        $fileModel = new FileModel();
        $registerData['files'] = $fileModel->getFile(columns: 'id, name');

        return view('templates/header', $headerData)
              .view('files/register', $registerData)
              .view('templates/footer');
    }
}
