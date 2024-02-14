<?php

namespace App\Controllers;

class APITest extends APIController
{
    public function index()
    {
        $data = [
            'a' => 1,
            'b' => 2,
            'shrebbles' => [
                1231,
                123213,
                13231
            ]
        ];
        return $this->respond($data, 200, 'AAAAAAAAAAAAAA');
    }
}
