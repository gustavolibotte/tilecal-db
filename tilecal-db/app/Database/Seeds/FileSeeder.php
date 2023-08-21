<?php

namespace App\Database\Seeds;

class FileSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('files.json');
        foreach ($jsonData as $fileData){
            $fileData['filedata'] = $this->readFile($fileData['filedata']);
            $file = new \App\Entities\File($fileData);
            $fileModel = new \App\Models\FileModel(isMigration: true);
            $fileModel->insertWithLog($file);
            if (str_starts_with($fileData['name'], 'deleted')){
                $fileModel->deleteWithLog($file->id);
            }
        }
    }
}
