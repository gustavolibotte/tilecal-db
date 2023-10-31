<?php

function joinPaths(array $subPaths): string
{
    $joinedPath = join(DIRECTORY_SEPARATOR, $subPaths);
    return $joinedPath;
}

function splitPath(string $path): array
{
    $splitted = explode(DIRECTORY_SEPARATOR, $path);
    $tail = array_pop($splitted);
    return [
        'base' => implode(DIRECTORY_SEPARATOR, $splitted),
        'tail' => $tail
    ];
}