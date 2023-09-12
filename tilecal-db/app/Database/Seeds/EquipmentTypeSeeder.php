<?php

namespace App\Database\Seeds;

use App\Entities\TypeHierarchy;
use App\Models\TypesHierarchyModel;

class EquipmentTypeSeeder extends BaseSeeder
{
    public function run()
    {
        service('authenticator')->setMigration();
        $jsonData = $this->readFile('2023-07-07-134759_types.json');
        $equipmentTypeModel = new \App\Models\EquipmentTypeModel();
        foreach ($jsonData as $row){
            $equipmentType = new \App\Entities\EquipmentType($row);
            $equipmentTypeModel->insert($equipmentType);
        }

        $typeHierarchyModel = new TypesHierarchyModel();
        foreach ($jsonData as $row){
            if (empty($row['hierarchy'])){
                continue;
            }
            $hierarchies = $this->getFormatedHierarchyArray($row);
            foreach ($hierarchies as $hierarchy){
                $typeHierarchyModel->insert($hierarchy);
            }
        }
    }

    protected function getFormatedHierarchyArray(array $row): array
    {
        $equipmentTypeModel = new \App\Models\EquipmentTypeModel();
        $nameToId = $equipmentTypeModel->getCustomArray('name', 'id');
        foreach($row['hierarchy'] as $relation){
            foreach($relation['children'] as &$child){
                $child = $nameToId[$child];
            }
            $hierarchies[] = new TypeHierarchy([
                'parent_type_id' => $row['id'],
                'children_type_ids' => $relation['children'],
                'max_count' => $relation['max_count']
            ]);
        }
        return $hierarchies;
    }
}
