<?php

function regex_match($value, $pattern, $fieldName, &$error): bool
{
    if (!(preg_match($pattern, $value))){
        $error = sprintf(
            '%s is not in the correct format.',
            $fieldName
        );
        return false;
    }
    return true;
}

function max_length($value, $limit, $fieldName, &$error): bool
{
    if (strlen($value) > $limit){
        $error = sprintf(
            '%s cannot exceed %d characters in length.',
            $fieldName,
            $limit
        );
    }
    return true;
}

function min_length($value, $limit, $fieldName, &$error): bool
{
    if (strlen($value) < $limit){
        $error = sprintf(
            '%s must be at least %d characters in length.',
            $fieldName,
            $limit
        );
    }
    return true;
}

function exact_length($value, $length, $fieldName, &$error): bool
{
    if (strlen($value) != $length){
        $error = sprintf(
            '%s must have exactly %d characters in length.',
            $fieldName,
            $length
        );
    }
    return true;
}