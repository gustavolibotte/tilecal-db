<?php

function unset_array_keys(array &$source, array $removeKeys)
{
    foreach ($removeKeys as $key){
        if (array_key_exists($key, $source)){
            unset($source[$key]);
        }
    }
}

function remove_value($key, array &$array): mixed
{
    $value = $array[$key];
    unset($array[$key]);
    return $value;
}