<?php

function null_empty(mixed $value): mixed
{
    if(empty($value)){
        return null;
    }
    return $value;
}