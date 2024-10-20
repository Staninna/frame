<?php

namespace models;

use Frame\Model\Model;
use ReflectionException;

class User extends Model
{
//    public int $id;
//    public string $naam;
//    public string $email;
//    public string $wachtwoord;

    // TODO: Timestamps created_at, updated_at

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = 'users';
    }

    public function tasks(): array
    {
        return $this->hasMany(Task::class);
    }
}