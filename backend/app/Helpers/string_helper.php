<?php

function snake2Capitalized(string $str)
{
    $str = ucwords(str_replace('_', ' ', $str));
    return $str;
}

function snake2CamelCase(string $str)
{
    $exploded = explode('_', $str);
    $newStr = ucfirst($exploded[0]);
    for ($i=1; $i < count($exploded); $i++){
        $newStr .= ucfirst($exploded[$i]);
    }
    return $newStr;
}

function snake2LowerCamelCase(string $str)
{
    $exploded = explode('_', $str);
    $newStr = $exploded[0];
    for ($i=1; $i < count($exploded); $i++){
        $newStr .= ucfirst($exploded[$i]);
    }
    return $newStr;
}

function makeLowerCamelCase($array)
{
    $str = $array[0];
    for ($i=1; $i < count($array); $i++){
        $str .= ucfirst($array[$i]);
    }
    return $str;
}

function makeSnakeCase(string $str): string
{
    $str = strtolower($str);
    $str = explode(' ', $str);
    $str = implode('_', $str);
    return $str;
}

function implodeWithouEmpty(string $separator, array $array): string
{   
    $first = true;
    $newStr = '';
    foreach ($array as $str){
        if (!empty($str)){
            if ($first){
                $newStr = $str;
                $first = false;
            } else{
                $newStr .= $separator;
                $newStr .= $str;
            }
        }
    }
    return $newStr;
}
