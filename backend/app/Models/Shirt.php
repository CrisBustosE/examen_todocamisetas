<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shirt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'titulo',
        'club',
        'pais',
        'tipo',
        'color',
        'precio',
        'precio_oferta',
        'detalles',
        'codigo_producto'
    ];

    // Una camiseta pertenece a un cliente
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'cliente_id');
    }

    // Relación Muchos a Muchos con Tallas (Sizes)
    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'shirt_size');
    }

    // TAREA 6: Lógica de negocio y JOIN manual
    public static function findWithFinalPrice(string $shirtId, string $clientId)
    {
        // 1. Buscamos al cliente para saber su categoría
        $client = Client::find($clientId);
        if (!$client) {
            throw new ModelNotFoundException("Cliente no encontrado.");
        }

        // 2. Realizamos el JOIN con las tallas como exige la rúbrica
        // Usamos GROUP_CONCAT para devolver las tallas en un solo string separado por comas
        $shirt = self::select(
            'shirts.*',
            DB::raw('GROUP_CONCAT(sizes.nombre) as tallas_disponibles')
        )
            ->leftJoin('shirt_size', 'shirts.id', '=', 'shirt_size.shirt_id')
            ->leftJoin('sizes', 'shirt_size.size_id', '=', 'sizes.id')
            ->where('shirts.id', $shirtId)
            ->groupBy('shirts.id')
            ->first();

        if (!$shirt) {
            throw new ModelNotFoundException("Camiseta no encontrada.");
        }

        // 3. Regla de Negocio: Cumplimiento estricto del enunciado
        if ($client->categoria === 'Preferencial' && $shirt->precio_oferta !== null) {
            $shirt->precio_final = $shirt->precio_oferta;
        } else {
            $shirt->precio_final = $shirt->precio;
        }

        /*
        * PROPUESTA EXTENDIDA - "Mejor Precio Garantizado"
        * Considera el porcentaje_oferta del cliente para todos los productos.
        * Pendiente confirmación del profesor sobre si aplica a clientes regulares.
        *
        * $basePrice = $shirt->precio;
        * $priceWithDiscount = $basePrice;
        * if ($client->porcentaje_oferta > 0) {
        *     $discountAmount = $basePrice * ($client->porcentaje_oferta / 100);
        *     $priceWithDiscount = (int) round($basePrice - $discountAmount);
        * }
        * if ($client->categoria === 'Preferencial' && $shirt->precio_oferta !== null) {
        *     $shirt->precio_final = min($shirt->precio_oferta, $priceWithDiscount);
        * } else {
        *     $shirt->precio_final = $priceWithDiscount;
        * }
        */

        // Retornamos el arreglo limpio
        return [
            'id'                 => $shirt->id,
            'titulo'             => $shirt->titulo,
            'club'               => $shirt->club,
            'pais'               => $shirt->pais,
            'tipo'               => $shirt->tipo,
            'color'              => $shirt->color,
            'detalles'           => $shirt->detalles,
            'codigo_producto'    => $shirt->codigo_producto,
            'tallas_disponibles' => $shirt->tallas_disponibles,
            'precio_final'       => $shirt->precio_final,
            'cliente_consultor'  => [
                'id'        => $client->id,
                'categoria' => $client->categoria,
            ],
        ];
    }
}
