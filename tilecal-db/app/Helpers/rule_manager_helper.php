<?php

function add_rule(array $fieldRuleSet, string $rule, ?string $error = null, string $where='end'): array
{   
    if ($where === 'end'){
        $fieldRuleSet['rules'][] = $rule;
    } elseif ($where === 'start'){
        $fieldRuleSet['rules'] = array_merge(
            [$rule],
            $fieldRuleSet['rules']
        );
    } else{
        throw new ValueError("where param only supports 'end' and 'start' values");
    }
    if (!is_null($error)){
        $ruleName = explode('[', $rule)[0];
        $fieldRuleSet['errors'][$ruleName] = $error;
    }
    return $fieldRuleSet;
}

function remove_rule(array $fieldRuleSet, string $oldRule): array
{
    helper('app_array');
    $foundKey = null;
    foreach($fieldRuleSet['rules'] as $key=>$rule){
        if (str_starts_with($rule, $oldRule)){
            $foundKey = $key;
            break;
        }
    }
    if (!is_null($foundKey)){
        remove_value($foundKey, $fieldRuleSet['rules']);
        if (array_key_exists($oldRule, $fieldRuleSet['errors'])){
            unset($fieldRuleSet['errors'][$oldRule]);
        }
    }
    return $fieldRuleSet;
}
