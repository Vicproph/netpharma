<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserRepository extends Repository
{

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function create(array $attributes)
    {
        $attributes['password'] = Hash::make($attributes['password']);
        return parent::create($attributes);
    }

    public function update(int $id, array $attributes)
    {
        $attributes['password'] = Hash::make($attributes['password']);
        return parent::update($id,$attributes);
    }
}