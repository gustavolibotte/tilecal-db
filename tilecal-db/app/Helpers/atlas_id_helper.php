<?php

function getInstituteMne(string $id): string
{
    return substr($id, 4, 3);
}

function getSequentialNumber(string $id): string
{
    return substr($id, 7, 7);
}