<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class Repository
{

    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->paginate(20);
    }

    public function get(int $id)
    {
        return $this->model->find($id);
    }

    public function delete(int $id)
    {
        return $this->get($id)?->delete();
    }

    public function create(array $attributes)
    {
        return $this->model::create($attributes);
    }

    public function update(int $id, array $attributes)
    {
        return $this->get($id)?->update($attributes);
    }
}
