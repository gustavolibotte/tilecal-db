<?php

function unset_array_keys(array &$source, array $removeKeys)
{
    foreach ($removeKeys as $key){
        if (array_key_exists($key, $source)){
            unset($source[$key]);
        }
    }
}

/**
 * Unsets a value from an array and returns it
 *
 * @param $key Key to the value to remove
 * @param array &$array Array to have its value removed
 * @param bool $error If false ignores keys that doesn't exist and returns null
 * 
 * @return mixed The removed value
 */ 
function remove_value($key, array &$array, bool $error=true): mixed
{
    if ((!$error) && (!array_key_exists($key, $array))){
        return null;
    }
    $value = $array[$key];
    unset($array[$key]);
    return $value;
}

function remove_values(array $keys, array &$array, bool $error=true): array
{
    $values = array();
    foreach ($keys as $key){
        $values[$key] = remove_value($key, $array, $error);
    }
    return $values;
}

function remove_value_with_default($key, array &$array, mixed $default=null): mixed
{
    if (!array_key_exists($key, $array)){
        return $default;
    }
    $value = $array[$key];
    unset($array[$key]);
    return $value;
}

/**
 * Uses dot_array_search to search $key in $array, if not found returns $default
 * If $default is null works the same as dot_array_search
 *
 * @param $key Value key
 * @param array $array Array to search
 * @param mixed $default Default value to return if a value for $key is not found
 * 
 * @return mixed The value or $default
 */ 
function dot_search_with_default($key, array $array, mixed $default=null) : mixed
{
    helper('array');
    $value = dot_array_search($key, $array);
    if (is_null($value)){
        return $default;
    } else {
        return $value;
    }
}

function remap_values(array $array, array $valueMap): array
{
    $remaped = $array;
    foreach ($remaped as &$remap){
        $remap = dot_search_with_default($remap, $valueMap, $remap);
    }
    return $remaped;
}

/**
 * Renames the keys from an array
 *
 * @param array $array Array to be remapped
 * @param array $keyMap Array with $oldName=>$newName for the $array keys
 * 
 * @return array $array with the keys renamed.
 */ 
function rename_array_keys(array $array, array $keyMap): array
{
    foreach ($keyMap as $oldName => $newName) {
        if (array_key_exists($oldName, $array)) {
            $array[$newName] = remove_value($oldName, $array);
        }
    }
    return $array;
}
