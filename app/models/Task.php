<?php

namespace app\models;

use ReflectionException;
use Frame\Model\Model;

class Task extends Model
{
//    public int $id;
//    public string $title;
//    public string $beschrijving;
//    public string $status;
//    public int $verval_datum; // unix timestamp
//    public string $prioriteit;

    // TODO: Timestamps created_at, updated_at


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = 'tasks';
    }

    public function subTasks(): array
    {
        return $this->hasMany(SubTask::class);
    }

    /**
     * @throws ReflectionException
     */
    public function users(): array
    {
        return $this->belongsToMany(User::class);
    }
}