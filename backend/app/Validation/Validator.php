<?php

namespace App\Validation;

class Validator{
    public static string $ruleBreak = 'break';

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
            foreach($ruleSet as $ruleName => $ruleConfig){
                $ruleParams = $ruleConfig['params'] ?? [];
                $baseError = '';
                $ruleRes = $ruleName($fieldName, $res['validatedData'], $baseError, $ruleParams);
                if ($ruleRes === false){
                    $res['errors'][$fieldName] = $ruleConfig['error']?? $baseError;
                    $res['valid'] = false;
                    break;
                } else if ($ruleRes === self::$ruleBreak){
                    break;
                }
            }
        }
        // echo json_encode($data);
        return $res;
    }
}