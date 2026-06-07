<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre_comercial', 'rut', 'direccion',
        'categoria', 'contacto_nombre', 'contacto_correo', 'porcentaje_oferta'
    ];

    // Un cliente puede tener muchas camisetas (Shirts) asignadas
    public function shirts(): HasMany
    {
        return $this->hasMany(Shirt::class,'cliente_id');
    }
}
