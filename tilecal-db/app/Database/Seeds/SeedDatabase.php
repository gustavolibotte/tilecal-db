<?php

namespace App\Database\Seeds;

class SeedDatabase extends BaseSeeder
{
    public function run()
    {
        $this->seedModels();
        $this->seedStatuses();
    }

    public function seedModels(){
        $jsonData = $this->readJSON('models.json');
        $modelModel = new \App\Models\ModelModel(isMigration: true);
        foreach ($jsonData as $row){
            $row["params"] = json_encode($row['params']);
            $modelModel->insert($row);
        }
    }

    public function seedStatuses(){
        $jsonData = $this->readJSON('statuses.json');
        $statusModel = new \App\Models\StatusModel(isMigration: true);
        foreach ($jsonData as $row){
            $status = new \App\Entities\Status($row);
            $statusModel->insert($status);
        }
    }

    public function seedLocations(){
        $jsonData = $this->readJSON('location.json');
        $locationModel = new \App\Models\LocationModel(isMigration: true);
        foreach ($jsonData as $row){
            $location = new \App\Entities\Location($row);
            $locationModel->insert($location);
        }
    }

    public function seedFiles(){
        $jsonData = $this->readJSON('files.json');
        foreach ($jsonData as $fileData){
            $fileData['filedata'] = file_get_contents(ROOTPATH . $fileData['filedata']);
            $file = new \App\Entities\File($fileData);
            $fileModel = new \App\Models\FileModel(isMigration: true);
            $fileModel->insert($file);
            if (str_starts_with($fileData['name'], 'deleted')){
                $fileModel->delete($file->id);
            }
        }
    }
}
