<?php

namespace App\Database\Seeds;

use App\Entities\Equipment;
use App\Models\DetectorLocationModel;
use App\Models\EquipmentModel;
use App\Models\EquipmentTypeModel;
use App\Models\StatusModel;
use App\Models\LocationModel;
use App\Models\InstituteModel;
use App\Models\TypeSlotModel;
use Faker\Factory;
use Faker\Generator;


class EquipmentSeeder extends BaseSeeder
{
    public static string $defaultSerialRegex = '^[A-Z0-9]{10}';
    protected EquipmentTypeModel $equipmentTypeModel;
    protected array $equipTypes;
    protected LocationModel $locationModel;
    protected array $locations;
    protected StatusModel $statusModel;
    protected array $statuses;
    protected InstituteModel $instituteModel;
    protected array $institutes;
    protected TypeSlotModel $typeSlotModel;
    protected array $slots;
    protected DetectorLocationModel $detectorLocationModel;
    protected array $detectorLocations;

    protected int $numCommentsCharacters = 100;
    protected $originalReturnType;
    protected float $spareChance;

    protected $fakers = [];

    protected array $nSamplesPerType = [
        'PMT' => 100,
        'FENICS' => 100,
        'PMT Block' => 100,
        'P2 LB Super Drawer' => 2,
        'Mini Drawer' => 10
    ];

    protected float $deleteChance = 0.03;

    public function __construct()
    {
        helper(['string', 'random']);
        $this->equipmentTypeModel = new EquipmentTypeModel();
        $this->equipTypes = $this->equipmentTypeModel->getCustomArray(keyField: 'name');
        $this->locationModel = new LocationModel();
        $this->locations = $this->locationModel ->getIdArray();
        $this->statusModel = new StatusModel();
        $this->statuses = $this->statusModel->getCustomArray(keyField: 'name');
        $this->instituteModel = new InstituteModel();
        $this->institutes = $this->instituteModel->getCustomArray(keyField: 'mne');
        $this->typeSlotModel = new TypeSlotModel();
        $this->slots = $this->typeSlotModel->getCustomArray(keyField: 'name');
        $this->detectorLocationModel = new DetectorLocationModel();
        $this->detectorLocations = $this->detectorLocationModel->getCustomArray(keyField: 'name');
    }
    public function run()
    {
        $equipmentModel = new EquipmentModel();
        foreach ($this->nSamplesPerType as $equipTypeName => $nSamples){
            $faker = Factory::create();
            foreach (range(1, $nSamples) as $i){
                $equip = $this->fakeEquipment($faker, $equipTypeName);
                $equipmentModel->insert($equip);
                if(apply_prob($this->deleteChance)){
                    $equipmentModel->delete($equip->id);
                }
            }
        }
    }

    protected function fakeEquipment(Generator &$faker, string $typeName): Equipment
    {
        $method = sprintf('fake%s', makeCamelCase($typeName));
        if (method_exists($this, $method)){
            return $this->$method($faker);
        } else {
            $equipType = $this->equipTypes[$typeName];
            $equip = new Equipment([
                'id' => $faker->unique()->regexify($equipType->id_regex),
                'type_id' => $equipType->id,
                'location_id' => $faker->randomElement($this->locations)->id,
                'status_id' => $faker->randomElement($this->statuses)->id,
                'owner_id' => $faker->randomElement($this->institutes)->id,
                'detector_location_id' => $faker->randomElement($this->detectorLocations)->id,
                'serial' => $faker->regexify(self::$defaultSerialRegex),
                'comments' => $faker->text($this->numCommentsCharacters),
                'type_params' => array_fill_keys(array_keys($equipType->params), null)
            ]);
            return $equip;
        }
    }

    protected function fakePmt(Generator &$faker): Equipment
    {
        $equipType = $this->equipTypes['PMT'];
        $equip = new Equipment([
            'id' => $faker->unique()->regexify($equipType->id_regex),
            'type_id' => $equipType->id,
            'location_id' => $faker->randomElement($this->locations)->id,
            'status_id' => $faker->randomElement($this->statuses)->id,
            'owner_id' => $faker->randomElement($this->institutes)->id,
            'detector_location_id' => $faker->randomElement($this->detectorLocations)->id,
            'comments' => $faker->text($this->numCommentsCharacters),
            'type_params' => array_fill_keys($equipType->params, null)
        ]);
        $equip->serial = substr($equip->id, 1, 6);
        return $equip;
    }
}
