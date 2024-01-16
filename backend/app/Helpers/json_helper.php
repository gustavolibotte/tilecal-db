<?php

function readJson(string $filepath, bool $asArray=false): stdClass|array
{
    $jsonString = file_get_contents($filepath);
    $jsonData = json_decode($jsonString, $asArray);
    return $jsonData;
}
