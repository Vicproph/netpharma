<?php

namespace App\Models;

use App\Models\Traits\RelationshipTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, RelationshipTrait;

    protected $with = ['user'];
    
    protected $fillable = [
        'title',
        'body',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
