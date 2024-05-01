<?php

/**
 * Returns null if a value is empty
 * @param mixed $value The value to be checked
 * @return mixed The value or null
 */
function null_empty(mixed $value): mixed
{
    if(empty($value)){
        return null;
    }
    return $value;
}

/**
 * Casts a value to a numeric type
 * @param mixed $value The value to be casted
 * @return mixed The casted value
 */
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

/**
 * Converts a stdClass to an array
 * @param stdClass|array $std The stdClass to be converted
 * @return array The converted array
 */
function std_to_array(stdClass|array $std): array
{
    return json_decode(json_encode($std), true);
}