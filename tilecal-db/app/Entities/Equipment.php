<?php

namespace App\Entities;

class Equipment extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'type_params' => 'json-array',
        'location_id' => 'integer',
        'status_id' => 'integer',
        'type_id' => 'integer',
        'institute_id' => 'integer',
        'beta' => '?float',
        'hv_nominal' => '?float',
        'qe' => '?float',
        'comments' => '?string',
        'slot_id' => '?integer'
    ];

    public function toArrayWithTypeParams(): array
    {
        $array = $this->toArray();
        foreach ($array['type_params'] as $key=>$value){
            $array[$key] = $value;
        }
        return $array;
    }

    public function getAtlasId(): ?string
    {
        if (empty($this->type_project)){
            return null;
        }
        elseif (empty($this->type_system)){
            return null;
        }
        elseif (empty($this->owner_mne)){
            return null;
        }
        $atlasId = sprintf(
            "%s%s%s%s",
            $this->type_project,
            $this->type_system,
            $this->owner_mne,
            $this->id
        );
        return $atlasId;
    }
}
