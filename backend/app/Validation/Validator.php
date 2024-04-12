<?php

namespace App\Validation;

class Validator{
    public static string $permitEmptyRule = 'permitEmpty';
    public static function validate(array $data, array $ruleSets): array
    {
        $res = [
            'valid' => true,
            'errors' => [],
            'validatedData' => $data
        ];
        foreach ($ruleSets as $fieldName => $ruleSet){
            foreach($ruleSet as $ruleName => $ruleConfig){
                // echo $fieldName;
                // echo $ruleName;
                // var_dump($data[$fieldName]);
                if ($ruleName === self::$permitEmptyRule){
                    if (empty($data[$fieldName])){
                        break;
                    } else {
                        continue;
                    }
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