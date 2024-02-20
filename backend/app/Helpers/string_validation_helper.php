<?php

function regex_match(mixed $value, string $pattern, string $fieldName, ?string &$error): bool
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

function max_length(mixed $value, int $limit, string $fieldName, ?string &$error): bool
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

function min_length(mixed $value, int $limit, string $fieldName, ?string &$error): bool
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

function exact_length(mixed $value, int $length, string $fieldName, ?string &$error): bool
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