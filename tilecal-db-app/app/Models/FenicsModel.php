<?php

namespace App\Models;

use App\Models\EquipmentModel;

class FenicsModel extends EquipmentModel{

    public function getEquipment($id=null){

        $columns = 'equipments.id, ';
        $columns .= 'equipments.location, ';
        $columns .= 'equipments.status, ';
        $columns .= 'equipments.parent_id, ';
        $columns .= 'equipments.comments, ';
        if (is_null($id)){
            $result = $this->select($columns)
                           ->where("models.name = 'fenics'")
                           ->join('models', 'equipments.model_id = models.id')
                           ->orderBy('equipments.updated_at', 'DESC')
                           ->findAll();
        }
        else{
            $result = $this->select($columns)
                           ->where("models.name = 'fenics'")
                           ->where(sprintf("equipments.id = '%s'", $id))
                           ->join('models', 'equipments.model_id = models.id')
                           ->orderBy('equipments.updated_at', 'DESC')
                           ->first();
        }

        return $result;
    }
}
