<?php

function div_wrap(string $divClass, array $components){
    $wrapped = sprintf('<div class="%s">', $divClass);
    foreach ($components as $component){
        $wrapped .= $component;
    }
    $wrapped .= '</div>';
    return $wrapped;
}

function user_input_field($id, $value, $fieldType, $fieldParams){
    helper('form');
    if ($fieldType == 'hidden'){
        if (is_null($value)){
            $value = "";
        }
        return form_hidden($id, $value);
    }
    $fieldParams['id'] = $id;
    $fieldParams['name'] = $id;
    $fieldParams['class'] = 'form-control';
    if ($fieldType == 'dropdown'){
        $fieldParams['selected'] = $value;
    } else{
        $fieldParams['value'] = $value;
    }
    $inputStr = call_user_func(sprintf('form_%s', $fieldType), $fieldParams);
    return $inputStr;
}

function form_help($id, $helpText){
    $helpStr = sprintf(
        '<div id="%s-help" class="form-text"> %s </div>',
        $id, $helpText
    );
    return $helpStr;
}

function form_alert(string $message, string $type='danger'){
    $alert = sprintf('<div class="alert alert-%s alert-dismissible" role="alert">', $type);
    $alert .= sprintf('<div>%s</div>', $message);
    $alert .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    $alert .= '</div>';
    return $alert;
}

function form_field(string $id,
                    string $fieldType,
                    array $fieldParams,
                    mixed $value=null,
                    bool $required=false,
                    string $helpText=null,
                    string $label=null){
    helper('form');
    helper('string');
    if ($fieldType == 'hidden'){
        $components = [user_input_field($id, $value, $fieldType, $fieldParams)];
    } else{
        if (is_null($label)){
            $label = snake2Capitalized($id);
        }
        if (is_null($helpText)){
            $helpText = "";
        }
        $labelText = ($required)? $label . '*:': $label . ':';
        $components = [
            form_label($labelText, $id, ['class' => 'col-form-label']),
            user_input_field($id, $value, $fieldType, $fieldParams),
            form_help($id, $helpText)
        ];
    }
    $fieldStr = div_wrap('mb-3', $components);
    return $fieldStr;
}

function get_model_form_class(string $typeName): string
{
    helper('string');
    if ($typeName != 'pmt'){
        return '\App\Library\Forms\EquipmentForm';
    }
    $typeName = snake2CamelCase($typeName);
    $formClass = sprintf('\App\Library\Forms\%sForm', $typeName);
    return $formClass;
}