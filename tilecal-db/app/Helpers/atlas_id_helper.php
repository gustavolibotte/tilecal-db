<?php

function getInstituteMne(string $id): string
{
    return substr($id, 4, 3);
}
