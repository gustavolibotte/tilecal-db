<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use App\Models\EquipmentModel;
use ValidationRules\DBFileRules;
use App\Models\HistModel;
use App\Entities\OperationLog;

class Files extends BaseController
{
    public function index()
    {
        $indexData['pageTitle'] = 'File Index';
        $indexData['pageHeader'] = 'File Index';
        return view('files/index', $indexData);
    }

    public function deletedIndex(){
        $headerData = $this->getHeaderData(pageTitle: 'Deleted File Index');
        $fileModel = new FileModel();
        $indexData['files'] = $fileModel->getFile(searchDeleted: true);
        return view('templates/header', $headerData)
              .view('files/index', $indexData)
              .view('templates/footer');
    }

    public function view($fileId){
        $fileModel = new FileModel();
        $fileInfo = $fileModel->getFile($fileId);
        $viewData['fileInfo'] = $fileInfo;
        $viewData['pageTitle'] = 'File view';
        $viewData['pageHeader'] = sprintf('File %s', $fileInfo->name);
        $viewData['infoHeader'] = 'File Info';
        $viewData['histories'] = (new HistModel())->getFileHist($fileInfo->id);

        return view('files/view', $viewData);
    }

    public function download($id){

        $fileModel = new FileModel();
        $file = $fileModel->getFile($id, 'name, mime, extension, filedata');
        $filename = $file->name . '.' . $file->extension;

        header('Content-Description: File Transfer');
        header(sprintf('Content-Type: %s', $file->mime));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($file->filedata));

        echo $file->filedata;
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

    public function uploadGet(){

        if(!$this->currentUser->canAlter()){
            return $this->httpErrorView(401);
        }
        helper('form');

        $headerData = $this->getHeaderData(pageTitle: 'File Upload');
        $registerData = $this->getRegisterData();

        return view('templates/header', $headerData)
              .view('files/register', $registerData)
              .view('templates/footer');
    }

    public function uploadPost(){

        if(!$this->currentUser->canAlter()){
            return $this->httpErrorView(401);
        }
        helper('form');

        $uploadedFile = $this->request->getFile('file');
        $formNonFileInputs = $this->request->getPost(['name', 'description']);
        $filepath = $uploadedFile->getPathname();

        if ($this->validate('fileUploadRules')){
            $registerData = $this->getRegisterData();
            $fileData = [
                "name" => $formNonFileInputs['name'],
                "mime" => $uploadedFile->getMimeType(),
                "extension"=> $uploadedFile->guessExtension(),
                "filedata" => file_get_contents($filepath),
                "description" => $formNonFileInputs['description'],
            ];
            $file = new \App\Entities\File($fileData);
            $fileModel = new FileModel();
            $fileModel->insert($file);
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
