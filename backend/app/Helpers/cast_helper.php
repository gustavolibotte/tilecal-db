<?php

function null_empty(mixed $value): mixed
{
    if(empty($value)){
        return null;
    }
    return $value;
}

function cast_to_numeric(mixed $value): mixed
{
    if(!is_numeric($value)){
        return $value;
    }
    if(is_int($value+0)){
        return (int)$value;
    } else{
        return (float)$value;
    }
}