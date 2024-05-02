<?php

namespace App\Validation;

/**
 * Class Validator
 */
class Validator{
    public static string $ruleBreak = 'break';

    /**
     * Validates an array of data against a set of rules
     * @param array $data Data to validate
     * @param array|string $ruleSets The rules to validate against.
     * If a string is passed, searches the attribute with that name in the Validation config file
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
     * It will have the field names as keys and the validated values as values.
     * Only the fields that had rules will be present. If you want to keep all fields,
     * create a rule set with an empty rule array for fields that do not need any validation.
     */
    public static function validate(array $data, array|string $ruleSets): array
    {
        $res = [
            'valid' => true,
            'errors' => [],
            'validatedData' => $data
        ];
        if (is_string($ruleSets)){
            $ruleSets = config('Validation')->$ruleSets;
        }
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
        $ruleKeys = array_keys($ruleSets);
        foreach (array_keys($res['validatedData']) as $fieldName){
            if (!in_array($fieldName, $ruleKeys)){
                unset($res['validatedData'][$fieldName]);
            }
        }
        return $res;
    }
}