<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $guarded = [];

    protected $casts = ['is_available' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeLowStock($query)
    {
        return $query->where('is_available', true)->whereColumn('stock', '<=', 'min_stock');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image || ! file_exists(public_path($this->image))) {
            return null;
        }

        return asset($this->image);
    }

    public function getPriceFormattedAttribute(): string
    {
        return 'Rp' . number_format($this->price, 0, ',', '.');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->is_available && $this->stock <= $this->min_stock;
    }

    // data untuk mengisi form edit
    public function toForm(): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'category_id'  => $this->category_id,
            'price'        => $this->price,
            'cost_price'   => $this->cost_price,
            'unit'         => $this->unit,
            'stock'        => $this->stock,
            'min_stock'    => $this->min_stock,
            'is_available' => $this->is_available,
            'image_url'    => $this->image_url,
        ];
    }
}