<?php

namespace App\Database\Seeds;

class FileSeeder extends BaseSeeder
{
    public function run()
    {
        helper('path');
        service('authenticator')->setMigration();
        $jsonData = $this->readFile('files.json');
        $fileModel = new \App\Models\FileModel(copyFile: true);
        foreach ($jsonData as $fileData){
            $file = new \App\Entities\File($fileData);
            $configPaths = config('Paths');
            $file->path = joinPaths([$configPaths->dataDirectory, $file->path]);
            $fileModel->insert($file);
            if (str_starts_with($fileData['name'], 'deleted')){
                $fileModel->delete($file->id);
            }
        }
    }
}
