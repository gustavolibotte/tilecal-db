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
        $fileData = json_decode($jsonString, true);
        $fileData['filedata'] = file_get_contents($fileData['filedata']);
        $fileModel = new FileModel();
        $fileModel->insert($fileData);
    }
}
