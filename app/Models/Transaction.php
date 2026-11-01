<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    protected $casts = ['paid_at' => 'datetime'];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}