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