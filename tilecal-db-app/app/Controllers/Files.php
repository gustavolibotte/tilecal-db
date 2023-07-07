<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use App\Models\EquipmentModel;
use ValidationRules\DBFileRules;

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

    public function download($id){

        $fileModel = new FileModel();
        $file = $fileModel->getFile($id, 'name, mime, filedata');

        header('Content-Description: File Transfer');
        header(sprintf('Content-Type: %s', $file['mime']));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $file['name']));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($file['filedata']));

        echo $file['filedata'];
    }

    public function getRegisterData($validationErrors=null, $preFilledValues=null){

        $fileModel = new FileModel();
        $registerData['files'] = $fileModel->getFile(columns: 'id, name');
        $registerData['validationErrors'] = $validationErrors;
        $registerData['formPlaceholders'] = [
            'name' => '2023_07_30_pmt_block_5628_test',
            'description' => 'Useful info about the file'
        ];
        $registerData['formRestrictions'] = [
            'name' => sprintf('Max %d characters', DBFileRules::$maxFilenameSize),
            'file' => sprintf('File max size is %d kB', DBFileRules::$maxFileSize),
            'description' => sprintf('Min %d characters and max %d characters',
                                     DBFileRules::$minDescriptionSize,
                                     DBFileRules::$maxDescriptionSize)
        ];
        $registerData['maxDescriptionLength'] = DBFileRules::$maxDescriptionSize;
        if (is_null($preFilledValues)){
            $registerData['preFilledValues'] = [
                'name' => null,
                'description' => null
            ];
        }
        else{
            $registerData['preFilledValues'] = $preFilledValues;
        }

        return $registerData;
    }

    public function registerGet(){
        helper('form');

        $headerData = $this->getHeaderData(pageTitle: 'File Upload');
        $registerData = $this->getRegisterData();

        return view('templates/header', $headerData)
              .view('files/register', $registerData)
              .view('templates/footer');
    }

    public function registerPost(){
        helper('form');

        $file = $this->request->getFile('file');
        $formNonFileInputs = $this->request->getPost(['name', 'description']);
        $filepath = $file->getPathname();
        $filetype = $file->getMimeType();

        if ($this->validate('fileUploadRules')){
            $registerData = $this->getRegisterData();
            $fileData = [
                "name" => $formNonFileInputs['name'],
                "filedata" => file_get_contents($filepath),
                "description" => $formNonFileInputs['description'],
            ];
        }
        else{
            $validationErrors = $this->validator->getErrors();
            $registerData = $this->getRegisterData(
                $validationErrors,
                $formNonFileInputs
            );
        }

        $headerData = $this->getHeaderData(pageTitle: 'File Upload');

        return view('templates/header', $headerData)
        .view('files/register', $registerData)
        .view('templates/footer');
    }

}
