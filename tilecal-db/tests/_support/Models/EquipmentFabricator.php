<?php

namespace Tests\Support\Models;

use App\Models\EquipmentModel;
use App\Models\ModelModel;
use App\Models\StatusModel;
use App\Models\LocationModel;
use App\Models\InstituteModel;
use App\Library\Validation\AtlasIdRules;
use App\Library\Forms\PmtForm;

class EquipmentFabricator extends EquipmentModel
{
    public ModelModel $modelModel;
    public array $models;
    public LocationModel $locationModel;
    public array $locations;
    public StatusModel $statusModel;
    public array $statuses;
    public InstituteModel $instituteModel;
    public array $institutes;

    public int $numCommentsCharacters = 100;
    public bool $firstCall = true;
    public $parentEquipment;

    public function initialize()
    {
        parent::initialize();
        $this->modelModel = new ModelModel(isMigration: $this->isMigration);
        $this->models = $this->modelModel->getIdArray();
        $this->locationModel = new LocationModel(isMigration: $this->isMigration);
        $this->locations = $this->locationModel ->getIdArray();
        $this->statusModel = new StatusModel(isMigration: $this->isMigration);
        $this->statuses = $this->statusModel->getIdArray();
        $this->instituteModel = new InstituteModel(isMigration: $this->isMigration);
        $this->institutes = $this->instituteModel->getIdArray();
    }

    public function getRandomEquipment(){
        return $this->fullQuery()->orderBy('rand()')->first();
    }

    public function fake(&$faker)
    {
        helper('string');
        $model = $faker->randomElement($this->models);
        try{
            $modelMethod = snake2CamelCase($model->name);
            $methodName = "fake{$modelMethod}";
            $fakeEquipment = $this->$methodName($faker);
        } catch (\BadMethodCallException $e) {
            $location = $faker->randomElement($this->locations);
            $status = $faker->randomElement($this->statuses);
            $institute = $faker->randomElement($this->institutes);
            $system = $faker->randomElement(array_keys(AtlasIdRules::$validSystems));
            $entityArray = [
                'id' => "20{$system}{$institute->mne}" . $faker->regexify(AtlasIdRules::$sequentialNumberRegex),
                'model_id' => $model->id,
                'location_id' => $location->id,
                'status_id' => $status->id,
                'owner_id' => $institute->id,
                'serial' => $faker->regexify('^[A-Z0-9]{10}'),
                'model_params' => [],
                'comments' => $faker->text($this->numCommentsCharacters)
            ];
            if (!empty($this->parentEquipment)){
                $entityArray['parent_id'] = $this->parentEquipment->id;
            }
            $fakeEquipment = new $this->returnType($entityArray);
        }
        if ($this->firstCall){
            $this->parentEquipment = $fakeEquipment;
            $this->firstCall = false;
        }
        return $fakeEquipment;
    }

    protected function fakePmt(&$faker)
    {
        $model = $this->models[PmtForm::$pmtId];  //PMT is ID 1
        $location = $faker->randomElement($this->locations);
        $status = $faker->randomElement($this->statuses);
        $institute = $this->institutes[PmtForm::$pmtOwnerId];   //PMT is always from CERN
        $entityArray = [
            'model_id' => $model->id,
            'location_id' => $location->id,
            'status_id' => $status->id,
            'owner_id' => $institute->id,
            'serial' => $faker->regexify(PmtForm::$validSerialRegex),
            'comments' => $faker->text($this->numCommentsCharacters),
            'model_params' => [
                'beta' => $faker->randomFloat(3, 6, 8),
                'nominal_hv' => $faker->numberBetween(600, 800),
                'qe' => $faker->randomFloat(1, 20, 25),
                'type' => $faker->randomElement(PmtForm::$validPmtTypeValues)
            ]
        ];
        $entityArray['id'] = "20LOCER0{$entityArray['serial']}";
        return new $this->returnType($entityArray);
    }
}
