<?php

namespace App\Library;

class User{

    public readonly string $username;
    public readonly array $roles;

    public function __construct(){}

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function asArray(): array
    {
        return [
            'username' => $this->username,
            'roles' => $this->roles
        ];
    }
}
