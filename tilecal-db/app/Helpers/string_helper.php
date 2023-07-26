<?php

function snake2Capitalized($str){
    $str = ucwords(str_replace('_', ' ', $str));
    return $str;
}