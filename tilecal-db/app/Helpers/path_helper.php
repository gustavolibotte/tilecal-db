<?php

function joinPaths(array $subPaths): string
{
    $joinedPath = join(DIRECTORY_SEPARATOR, $subPaths);
    return $joinedPath;
}

function has_extension(string $path): bool
{
    $exploded = explode('.', $path);
    return count($exploded) > 1;
}
