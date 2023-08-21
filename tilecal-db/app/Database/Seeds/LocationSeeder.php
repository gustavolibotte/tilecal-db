<?php

namespace App\Database\Seeds;

class LocationSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('2023-07-07-134759_locations.json');
        $instituteModel = new \App\Models\InstituteModel(isMigration: true);
        $locationModel = new \App\Models\LocationModel(isMigration: true);
        foreach ($jsonData as $row){
            $institute = $instituteModel->where('mne', $row['institute_mne'])->first();
            $location = new \App\Entities\Location($row);
            if (!is_null($institute)){
                $location->institute_id = $institute->id; 
            }
            $locationModel->insertWithLog($location);
        }
    }
}
