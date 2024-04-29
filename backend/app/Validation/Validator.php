<?php

namespace App\Validation;

class Validator{
    public static string $permitEmptyRule = 'permitEmpty';
    public static string $removeEmptyRule = 'removeEmpty';

    /**
     * Validates an array of data against a set of rules
     * @param array $data Data to validate
     * @param array $ruleSets Rules to validate against
     * The structure of the $ruleSets array should be:
     * [
     *    'field_name' => [
     *       'rule_name' => [
     *         'params' => [],
     *         'error' => ''
     *      ]
     *   ]
     * ]
     * @return array
     * The return value is an array with the following keys:
     * 'valid' - a boolean indicating if the data is valid
     * 'errors' - an array of error messages
     * 'validatedData' - the data that was validated
     * The 'errors' array will have the field names as keys and the error messages as values
     * The 'validatedData' array will have the field names as keys and the validated values as values
     */
    public static function validate(array $data, array $ruleSets): array
    {
        $res = [
            'valid' => true,
            'errors' => [],
            'validatedData' => $data
        ];
        foreach ($ruleSets as $fieldName => $ruleSet){
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
            if (!$res['valid']){
                break;
            }
        }
        return $res;
    }
}