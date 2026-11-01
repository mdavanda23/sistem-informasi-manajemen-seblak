<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $guarded = [];

    protected $casts = ['is_open' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}