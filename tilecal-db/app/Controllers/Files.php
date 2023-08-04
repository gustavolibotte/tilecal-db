<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use App\Library\Forms\FileForm;
use App\Models\HistModel;

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

    public function upload(){
        helper(['form', 'string', 'url']);
        $session = \Config\Services::session();

        if ($this->request->is('post')){
            $form = new FileForm($this->request);
            if ($form->validate()){
                $file = $form->getEntity();
                $fileModel = new FileModel();
                $fileModel->insert($file);
                $session->setFlashdata('success', true);
                return redirect('files/upload');
            };
        } else{
            $form = new FileForm();
        }
        
        $title = 'Upload File';
        $registerData['pageTitle'] = $title;
        $registerData['pageHeader'] = $title;
        $registerData['forms'] = ['File Info'=> $form];
        $registerData['successMessage'] = $session->getFlashdata('success');
        $registerData['formUrl'] = uri_string();
        $registerData['cancelUrl'] = base_url('files');
        return view('files/register', $registerData);
    }

}
