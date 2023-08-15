<?php

namespace App\Entities;

class Equipment extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        "model_params" => "json-array",
        "location_id" => "integer",
        "status_id" => "integer",
        "model_id" => "integer",
        "institute_id" => "integer"
    ];

    public function toArrayWithModelParams(): array
    {
        $array = $this->toArray();
        foreach ($array['model_params'] as $key=>$value){
            $array[$key] = $value;
        }
        return $array;
    }
}
