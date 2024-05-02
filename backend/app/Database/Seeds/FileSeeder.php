<?php

namespace App\Database\Seeds;
use CodeIgniter\Files\File;
use App\Validation\Validator;

class FileSeeder extends BaseSeeder
{
    public function run()
    {
        helper('path');
        $jsonData = $this->readFile('files.json');
        $fileModel = new \App\Models\FileModel();
        foreach ($jsonData as $fileData){
            $validationRes = Validator::validate($fileData, 'fileCreateRules');
            $validData = $validationRes['validatedData'];
            $fileModel->insert($validData);
            if (str_starts_with($validData['name'], 'deleted')){
                $fileModel->delete($validData['id']);
            }
        }
    }
}
