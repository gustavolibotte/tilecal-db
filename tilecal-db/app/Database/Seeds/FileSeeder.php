<?php

namespace App\Database\Seeds;

use App\Models\FileModel;

class FileSeeder extends BaseSeeder
{
    public function run()
    {
        $seedDataPath = ROOTPATH . '/app/Database/Seeds/seed_data/files.json';
        $jsonString = file_get_contents($seedDataPath);
        $rows = json_decode($jsonString, true);
        foreach ($rows as $fileData){
            $fileData['filedata'] = file_get_contents(ROOTPATH . $fileData['filedata']);
            $file = new \App\Entities\File($fileData);
            $fileModel = new FileModel(migration: true);
            $fileModel->insert($file);
            if (str_starts_with($fileData['name'], 'deleted')){
                $fileModel->delete($file->id);
            }
        }
    }
}
