<?php

function div_wrap($divClass, $labelStr, $inputStr, $helpStr){
    $wrapped = sprintf(
        '<div class="%s">%s%s%s</div>',
        $divClass, $labelStr, $inputStr, $helpStr
    );
    return $wrapped;
}

function user_input_field($id, $inputInfo){
    helper('form');
    $inputStr = call_user_func(sprintf('form_%s', $inputInfo['formType']), $inputInfo['helperParams']);
    return $inputStr;
}

function form_help($id, $helpInfo){
    $helpStr = sprintf(
        '<div id="%s-help" class="form-text"> %s </div>',
        $id, $helpInfo['text']
    );
    return $helpStr;
}

function form_field($fieldInfo){
    helper('form');
    $fieldStr = div_wrap(
        'mb-3',
        form_label($fieldInfo['labelInfo']['text'], $fieldInfo['id'], ['class' => 'col-form-label']),
        user_input_field($fieldInfo['id'], $fieldInfo['inputInfo']),
        form_help($fieldInfo['id'], $fieldInfo['helpInfo'])
    );
    return $fieldStr;
}
