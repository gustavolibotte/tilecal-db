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
 * 
 * @return mixed The removed value
 */ 
function remove_value($key, array &$array): mixed
{
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