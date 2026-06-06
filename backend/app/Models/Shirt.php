<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shirt extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'titulo', 'club', 'pais', 'tipo',
        'color', 'precio', 'precio_oferta', 'detalles', 'codigo_producto'
    ];

    // Una camiseta pertenece a un cliente
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Relación Muchos a Muchos con Tallas (Sizes)
    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'shirt_size');
    }
}
