<?php

namespace models;

use Frame\Model\Model;
use ReflectionException;

class SubTask extends Model
{
//    public int $id;
//    public string $title;
//    public string $beschrijving;
    public string $status;
    // TODO: Timestamps created_at, updated_at

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = 'sub_tasks';
    }

    /**
     * @throws ReflectionException
     */
    public function task(): Task
    {
        return $this->belongsTo(Task::class);
    }
}