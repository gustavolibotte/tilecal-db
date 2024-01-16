<?php

function default_view(string $name, array $data=[], array $options=[]): string
{   $data['user'] = service('authenticator')->getUser();
    return view($name, $data, $options);
}