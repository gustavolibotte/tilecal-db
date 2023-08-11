<?php

function joinPaths(array $subPaths): string
{
    $joinedPath = join(DIRECTORY_SEPARATOR, $subPaths);
    return $joinedPath;
}
