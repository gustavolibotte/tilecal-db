<?php

function random_choice($array){
    $key = array_rand($array);
    return $array[$key];
}

/**
 * Returns a boolean with $prob probability ofbeing true.
 * Precision up to 3 decimals
 *
 * @param float $prob Probability of function evaluation to be true
 * 
 * @return bool Probabilistic boolean value
 */
function apply_prob(float $prob): bool{
    $randomNumber = mt_rand(0,999);
    $approved = $randomNumber<($prob*1000);
    return $approved;
}
