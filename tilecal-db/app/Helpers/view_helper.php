<?php

function default_view(string $name, array $data=[], array $options=[]): string
{
    helper('authentication');
    $data['user'] = getUser();
    return view($name, $data, $options);
}