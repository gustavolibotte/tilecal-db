<?php

function choice($array){
    $key = array_rand($array);
    return $array[$key];
}
