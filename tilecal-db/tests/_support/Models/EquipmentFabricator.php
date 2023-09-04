<?php

namespace Tests\Support\Models;

use App\Models\EquipmentModel;
use App\Models\EquipmentTypeModel;
use App\Models\StatusModel;
use App\Models\LocationModel;
use App\Models\InstituteModel;
use App\Library\Validation\AtlasIdRules;
use App\Library\Forms\PmtForm;

class EquipmentFabricator extends EquipmentModel
{
    public EquipmentTypeModel $equipmentTypeModel;
    public array $types;
    public LocationModel $locationModel;
    public array $locations;
    public StatusModel $statusModel;
    public array $statuses;
    public InstituteModel $instituteModel;
    public array $institutes;

    public int $numCommentsCharacters = 100;

    public function initialize()
    {
        parent::initialize();
        $this->equipmentTypeModel = new EquipmentTypeModel();
        $this->types = $this->equipmentTypeModel->getIdArray();
        $this->locationModel = new LocationModel();
        $this->locations = $this->locationModel ->getIdArray();
        $this->statusModel = new StatusModel();
        $this->statuses = $this->statusModel->getIdArray();
        $this->instituteModel = new InstituteModel();
        $this->institutes = $this->instituteModel->getIdArray();
    }

    public function getRandomEquipment(){
        return $this->fullQuery()->orderBy('rand()')->first();
    }

    public function fake(&$faker)
    {
        helper('string');
        $type = $faker->randomElement($this->types);
        try{
            $typeMethod = snake2CamelCase($type->name);
            $methodName = "fake{$typeMethod}";
            $fakeEquipment = $this->$methodName($faker);
        } catch (\BadMethodCallException $e) {
            $location = $faker->randomElement($this->locations);
            $status = $faker->randomElement($this->statuses);
            $institute = $faker->randomElement($this->institutes);
            $system = $faker->randomElement(array_keys(AtlasIdRules::$validSystems));
            $entityArray = [
                'id' => "20{$system}{$institute->mne}" . $faker->regexify(AtlasIdRules::$sequentialNumberRegex),
                'type_id' => $type->id,
                'location_id' => $location->id,
                'status_id' => $status->id,
                'owner_id' => $institute->id,
                'serial' => $faker->regexify('^[A-Z0-9]{10}'),
                'type_params' => [],
                'comments' => $faker->text($this->numCommentsCharacters)
            ];
            $fakeEquipment = new $this->returnType($entityArray);
        }
        return $fakeEquipment;
    }

    protected function fakePmt(&$faker)
    {
        $type = $this->types[PmtForm::$pmtId];  //PMT is ID 1
        $location = $faker->randomElement($this->locations);
        $status = $faker->randomElement($this->statuses);
        $institute = $this->institutes[PmtForm::$pmtOwnerId];   //PMT is always from CERN
        $entityArray = [
            'type_id' => $type->id,
            'location_id' => $location->id,
            'status_id' => $status->id,
            'owner_id' => $institute->id,
            'serial' => $faker->regexify(PmtForm::$validSerialRegex),
            'comments' => $faker->text($this->numCommentsCharacters),
            'type_params' => [
                'beta' => $faker->randomFloat(3, 6, 8),
                'nominal_hv' => $faker->numberBetween(600, 800),
                'qe' => $faker->randomFloat(1, 20, 25),
                'model' => $faker->randomElement(PmtForm::$validPmtModelValues)
            ]
        ];
        $entityArray['id'] = "20LOCER0{$entityArray['serial']}";
        return new $this->returnType($entityArray);
    }
}
