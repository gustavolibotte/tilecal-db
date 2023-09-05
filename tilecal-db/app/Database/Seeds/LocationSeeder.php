<?php

namespace App\Database\Seeds;

class LocationSeeder extends BaseSeeder
{
    public function run()
    {
        service('authenticator')->setMigration();
        $jsonData = $this->readFile('2023-07-07-134759_locations.json');
        $instituteModel = new \App\Models\InstituteModel();
        $locationModel = new \App\Models\LocationModel();
        foreach ($jsonData as $row){
            $institute = $instituteModel->where('mne', $row['institute_mne'])->first();
            $location = new \App\Entities\Location($row);
            if (!is_null($institute)){
                $location->institute_id = $institute->id; 
            }
            $locationModel->insert($location);
        }
    }
}
