<?php

namespace Tests\Support\Models;

use App\Models\EquipmentModel;
use App\Models\EquipmentTypeModel;
use App\Models\StatusModel;
use App\Models\LocationModel;
use App\Models\InstituteModel;
use App\Library\Forms\PmtForm;
use App\Models\AllowedTypeSlotModel;
use App\Models\TypeSlotModel;
use CodeIgniter\Entity\Entity;
use Faker\Generator;

class EquipmentFabricator extends EquipmentModel
{
    public static string $defaultSerialRegex = '^[A-Z0-9]{10}';
    public static string $defaultIdSequentialNumber = '^[A-Z0-9]{7}';
    public EquipmentTypeModel $equipmentTypeModel;
    public array $types;
    public array $typesById;
    public LocationModel $locationModel;
    public array $locations;
    public StatusModel $statusModel;
    public array $statuses;
    public InstituteModel $instituteModel;
    public array $institutes;
    public TypeSlotModel $typeSlotModel;
    public array $slots;

    public int $numCommentsCharacters = 100;
    public $originalReturnType;
    public float $spareChance;

    public function __construct(float $spareChance)
    {
        parent::__construct();
        $this->spareChance = $spareChance;
    }

    public function initialize()
    {
        parent::initialize();
        helper(['string', 'random']);
        $this->equipmentTypeModel = new EquipmentTypeModel();
        $this->types = $this->equipmentTypeModel->getCustomArray(keyField: 'name');
        $this->typesById = $this->equipmentTypeModel->getIdArray();
        $this->locationModel = new LocationModel();
        $this->locations = $this->locationModel ->getIdArray();
        $this->statusModel = new StatusModel();
        $this->statuses = $this->statusModel->getCustomArray(keyField: 'name');
        $this->instituteModel = new InstituteModel();
        $this->institutes = $this->instituteModel->getIdArray();
        $this->originalReturnType = $this->returnType;
        $this->typeSlotModel = new TypeSlotModel();
        $this->slots = $this->typeSlotModel->getCustomArray(keyField: 'name');
    }

    public function queryRandomEquipment(){
        return $this->fullQuery()->orderBy('rand()')->first();
    }

    public function setAsArray(): self
    {
        $this->returnType = 'array';
        return $this;
    }

    public function setAsObject(): self
    {
        $this->returnType = $this->originalReturnType;
        return $this;
    }

    public function fake(Generator &$faker): array
    {
        $pmtBlock = $this->fakePMTBlock($faker);
        $divider = $this->fakeEquipment($faker, 'hv_divider', $pmtBlock->id, 'pmt_block_hv_divider_slot');
        $divider->model = 'active';
        $fenics = $this->fakeEquipment($faker, 'fenics', $pmtBlock->id, 'pmt_block_electronics_slot');
        $pmt = $this->fakePMT($faker, $pmtBlock->id, 'pmt_block_pmt_slot');
        $equipments = [
            $pmtBlock,
            $divider,
            $fenics,
            $pmt
        ];
        $spares = $this->makeSpares($faker, $equipments);
        $equipments = array_merge($equipments, $spares);
        return $equipments;
    }

    protected function fakePmt(Generator &$faker, string $parentId=null, string $slotName=null): Entity
    {
        $type = $this->types['pmt'];
        $location = $faker->randomElement($this->locations);
        $status = $faker->randomElement($this->statuses);
        $institute = $this->institutes[PmtForm::$pmtOwnerId];
        $entity = new $this->originalReturnType([
            'parent_id' => $parentId,
            'slot_id' => empty($slotName)? null: $this->slots[$slotName]->id,
            'type_id' => $type->id,
            'location_id' => $location->id,
            'status_id' => $status->id,
            'owner_id' => $institute->id,
            'id' => $faker->regexify($type->id_regex),
            'comments' => $faker->text($this->numCommentsCharacters),
            'type_params' => [
                'beta' => $faker->randomFloat(3, 6, 8),
                'nominal_hv' => $faker->numberBetween(600, 800),
                'qe' => $faker->randomFloat(1, 20, 25),
                'model' => $faker->randomElement(PmtForm::$validPmtModelValues)
            ]
        ]);
        $entity->serial = substr($entity->id, 1, 6);
        return $entity;
    }

    protected function fakePmtBlock(Generator &$faker, string $parentId=null): Entity
    {
        $type = $this->types['pmt_block'];
        $location = $faker->randomElement($this->locations);
        $status = $faker->randomElement($this->statuses);
        $institute = $faker->randomElement($this->institutes);
        $pos = $faker->randomNumber(2, false);
        $entity = new $this->originalReturnType([
            'id' => $faker->regexify($type->id_regex),
            'type_id' => $type->id,
            'parent_id' => $parentId,
            'slot_id' => empty($slotName)? null: $this->slots[$slotName]->id,
            'location_id' => $location->id,
            'status_id' => $status->id,
            'owner_id' => $institute->id,
            'serial' => $faker->regexify(self::$defaultSerialRegex),
            'comments' => $faker->text($this->numCommentsCharacters),
            'type_params' => []
            // 'type_params' => [
            //     'phase1_pos' => $pos,
            //     'phase2_pos' => $pos,
            // ]
        ]);
        return $entity;
    }

    public function makeSpares(Generator &$faker, array $equipments): array
    {
        $spares = [];
        foreach($equipments as $equipment){
            if(apply_prob($this->spareChance)){
                $type = $this->typesById[$equipment->type_id];
                $spare = $this->fakeEquipment($faker, $type->name);
                $spare->status_id = $this->statuses['Spare']->id;
                $spare->parent_id = null;
                $spares[] = $spare;
            }
        }
        return $spares;
    }

    protected function fakeEquipment(Generator &$faker, string $typeName, string $parentId=null, string $slotName=null): Entity
    {
        $method = sprintf('fake%s', snake2CamelCase($typeName));
        try{
            return $this->$method($faker, $parentId);
        } catch (\BadMethodCallException $e) {
            $type = $this->types[$typeName];
            $location = $faker->randomElement($this->locations);
            $status = $faker->randomElement($this->statuses);
            $institute = $faker->randomElement($this->institutes);
            $entity = new $this->originalReturnType([
                'id' => $faker->regexify($type->id_regex),
                'type_id' => $type->id,
                'parent_id' => $parentId,
                'slot_id' => empty($slotName)? null: $this->slots[$slotName]->id,
                'location_id' => $location->id,
                'status_id' => $status->id,
                'owner_id' => $institute->id,
                'serial' => $faker->regexify(self::$defaultSerialRegex),
                'comments' => $faker->text($this->numCommentsCharacters),
                'type_params' => []
            ]);
            return $entity;
        }
    }
}
