<?php

function snake2Capitalized($str){
    $str = ucwords(str_replace('_', ' ', $str));
    return $str;
}

function snake2CamelCase($str){
    $exploded = explode('_', $str);
    $newStr = ucfirst($exploded[0]);
    for ($i=1; $i < count($exploded); $i++){
        $newStr .= ucfirst($exploded[$i]);
    }
    return $newStr;
}

function snake2LowerCamelCase($str){
    $exploded = explode('_', $str);
    $newStr = $exploded[0];
    for ($i=1; $i < count($exploded); $i++){
        $newStr .= ucfirst($exploded[$i]);
    }
    return $newStr;
}

function makeLowerCamelCase($array){
    $str = $array[0];
    for ($i=1; $i < count($array); $i++){
        $str .= ucfirst($array[$i]);
    }
    return $str;
}