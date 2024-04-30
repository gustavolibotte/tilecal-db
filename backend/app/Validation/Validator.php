<?php

namespace App\Validation;

class Validator{
    public static string $permitEmptyRule = 'permitEmpty';
    public static string $removeEmptyRule = 'removeEmpty';

    /**
     * Validates an array of data against a set of rules
     * @param array $data Data to validate
     * @param array $ruleSets Rules to validate against
     * 
     * The structure of the $ruleSets array should be:
     * ```[
     *    'field_name' => [
     *       'rule_name' => [
     *         'params' => [],
     *         'error' => ''
     *      ]
     *   ]
     * ]```
     * @return array
     * 
     * The return value is an array with the following keys:
     * 
     * - `valid` - a boolean indicating if the data is valid
     * - `errors` - an array of error messages.
     * It will have the field names as keys and the error messages as values
     * - `validatedData` - the data that was validated.
     * It will have the field names as keys and the validated values as values
     */
    public static function validate(array $data, array $ruleSets): array
    {
        $res = [
            'valid' => true,
            'errors' => [],
            'validatedData' => $data
        ];
        foreach ($ruleSets as $fieldName => $ruleSet){
            $fieldRes = self::validateField($fieldName, $res['validatedData'], $ruleSet);
            if (!$fieldRes['valid']){
                $res['errors'] = $fieldRes['errors'];
                $res['valid'] = false;
                return $res;
            }
        }
        return $res;
    }

    public static function validateField(string $fieldName, array $data, array $ruleSet): array
    {
        $res = [
            'valid' => true,
            'errors' => [],
            'validatedData' => $data
        ];
        foreach($ruleSet as $ruleName => $ruleConfig){
            $isEmpty = empty($data[$fieldName]);
            if ($ruleName === self::$permitEmptyRule && $isEmpty){
                if (empty($data[$fieldName])){
                    break;
                } else {
                    continue;
                }
            } else if ($ruleName === self::$removeEmptyRule && $isEmpty){
                unset($res['validatedData'][$fieldName]);
                continue;
            }
            $ruleParams = $ruleConfig['params'] ?? [];
            $ruleError = $ruleConfig['error']?? '';
            if (!$ruleName($fieldName, $res['validatedData'], $ruleError, $ruleParams)){
                $res['errors'][$fieldName] = $ruleError;
                $res['valid'] = false;
                break;
            }
        }
        return $res;
    }
}