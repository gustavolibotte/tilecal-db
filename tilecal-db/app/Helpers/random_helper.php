<?php

function random_choice($array){
    $key = array_rand($array);
    return $array[$key];
}
