<?php

namespace App\Validation;

abstract class RuleSet{

    public function regexErrorMessage(string $fieldName): string
    {
        return sprintf(
            '%s is not in the correct format.',
            $fieldName
        );
    }

    public function maxLengthMessage(string $fieldName, int $maxLength): string
    {
        return sprintf(
            '%s cannot exceed %d characters in length.',
            $fieldName,
            $maxLength
        );
    }

    public function exactLengthMessage(string $fieldName, int $exactLength): string
    {
        return sprintf(
            '%s must have exactly %d characters in length.',
            $fieldName,
            $exactLength
        );
    }
}