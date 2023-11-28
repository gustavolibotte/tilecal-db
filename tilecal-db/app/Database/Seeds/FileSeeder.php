<?php

namespace App\Database\Seeds;
use CodeIgniter\Files\File;

class FileSeeder extends BaseSeeder
{
    public function run()
    {
        helper('path');
        $jsonData = $this->readFile('files.json');
        $fileModel = new \App\Models\FileModel(copyFile: true);
        foreach ($jsonData as $fileData){
            $filepath =  joinPaths([config('Paths')->dataDirectory, $fileData['path']]);
            $file = new \App\Entities\File();
            $file->fileObj = new File($filepath);
            $file->id = $fileData['id'];
            $file->name = $fileData['name'];
            $file->description = $fileData['description'];
            $file->copy = true;
            $fileModel->insert($file);
            if (str_starts_with($fileData['name'], 'deleted')){
                $fileModel->delete($file->id);
            }
        }
    }
}
