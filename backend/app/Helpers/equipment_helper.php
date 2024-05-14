<?php
use App\Entities\AtlasId;
use App\Validation\EquipmentRules;

/**
 * Preprocesses an equipment id to be used in the database
 * If the id is a 14 character code, it is considered an ATLAS ID
 * and has the sequential number extracted.
 * If the id is a 7 character code it is considered as 
 * sequential number and it is returned.
 * If the id has the string 'CHI10V', it is considered a mainboard produced by Chicago
 * and has the sequential number is extracted.
 * This is needed because the id from Chicago mainboards do not have the same format as the ATLAS ID
 * This function should be changed together with EquipmentRules::equipment_id_is_sanitized
 * in the validation module
 * @param string $id
 * @return string
 * @throws \Exception
 */
function preprocessEquipmentId(?string $id): string
{
    helper('string_validation');
    $strLength = strlen($id);
    if (empty($id)){
        return '';
    } else if ($strLength === EquipmentRules::$sequentialNumberLength){
        // Id is a sequential number
        return $id;
    } else if ($strLength === EquipmentRules::$atlasIdLength){
        // Id is an ATLAS ID
        $atlasId = new AtlasId(['id' => $id]);
        return $atlasId->sequential;
    } else if (str_contains($id, 'CHI10V')){
        // Id is a mainboard produced by Chicago
        $value = str_replace('V', '', $id);
        $atlasId = new AtlasId(['id' => $value]);
        return $atlasId->sequential;
    }else {
        throw new \Exception("Invalid equipment id");
    }
}