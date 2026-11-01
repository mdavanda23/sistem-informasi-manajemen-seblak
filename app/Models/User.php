<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'outlet_id', 'name', 'email', 'role', 'password', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function homeRoute(): string
    {
        return $this->role === 'owner' ? route('owner.dashboard') : route('kasir.dashboard');
    }

    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)->explode(' ')
            ->map(fn ($w) => Str::upper(Str::substr($w, 0, 1)))
            ->take(2)->implode('');
    }
}