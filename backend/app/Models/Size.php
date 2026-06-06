<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Size extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    // Relación Muchos a Muchos con Camisetas (Shirts)
    public function Shirts(): BelongsToMany
    {
        return $this->belongsToMany(Shirt::class, 'shirt_size');
    }
}
