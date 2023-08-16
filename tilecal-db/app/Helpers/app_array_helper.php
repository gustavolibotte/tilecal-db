<?php

function unset_array_keys(array $source, array $removeKeys): array
{
    return array_diff_key($source, array_flip($removeKeys));
}