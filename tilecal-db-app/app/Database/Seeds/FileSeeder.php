<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\FileModel;

class FileSeeder extends Seeder
{
    public function run()
    {
        $seedDataPath = '/root/tilecal-db/app/Database/Seeds/seed_data/files.json';
        $jsonString = file_get_contents($seedDataPath);
        $rows = json_decode($jsonString, true);
        foreach ($rows as $fileData){
            $fileData['filedata'] = file_get_contents($fileData['filedata']);
            $file = new \App\Entities\File($fileData);
            $fileModel = new FileModel();
            $fileModel->insert($file);
            if (str_starts_with($fileData['name'], 'deleted')){
                $fileModel->delete($file->id);
            }
        }
    }
}
