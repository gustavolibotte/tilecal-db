<?php

namespace App\Models;


class Equipments2EquipmentsModel extends LogModel
{
    protected $table            = 'equipments_to_equipments';
    protected $useAutoIncrement = true;
    protected $returnType       =  \App\Entities\Equipment2Equipment::class;
    protected $allowedFields    = ['equipment_a_id', 'equipment_b_id', 'type'];
}
