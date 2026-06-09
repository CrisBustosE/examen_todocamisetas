<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Shirt",
    title: "Shirt",
    required: ["cliente_id", "titulo", "club", "pais", "tipo", "color", "precio", "codigo_producto"]
)]
class ShirtSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;
    #[OA\Property(property: "cliente_id", type: "integer", example: 1)]
    public int $cliente_id;
    #[OA\Property(property: "titulo", type: "string", example: "Camiseta Local 2025")]
    public string $titulo;
    #[OA\Property(property: "club", type: "string", example: "Selección Chilena")]
    public string $club;
    #[OA\Property(property: "pais", type: "string", example: "Chile")]
    public string $pais;
    #[OA\Property(property: "tipo", type: "string", example: "Local")]
    public string $tipo;
    #[OA\Property(property: "color", type: "string", example: "Rojo")]
    public string $color;
    #[OA\Property(property: "precio", type: "integer", example: 45000)]
    public int $precio;
    #[OA\Property(property: "precio_oferta", type: "integer", nullable: true, example: 35000)]
    public ?int $precio_oferta;
    #[OA\Property(property: "detalles", type: "string", nullable: true, example: "Edición especial")]
    public ?string $detalles;
    #[OA\Property(property: "codigo_producto", type: "string", example: "SCL2025L")]
    public string $codigo_producto;
}

#[OA\Schema(
    schema: "ShirtInput",
    title: "Shirt Input",
    required: ["cliente_id", "titulo", "club", "pais", "tipo", "color", "precio", "codigo_producto", "sizes_ids"]
)]
class ShirtInputSchema
{
    #[OA\Property(property: "cliente_id", type: "integer", example: 1)]
    public int $cliente_id;
    #[OA\Property(property: "titulo", type: "string", example: "Camiseta Local 2025")]
    public string $titulo;
    #[OA\Property(property: "club", type: "string", example: "Selección Chilena")]
    public string $club;
    #[OA\Property(property: "pais", type: "string", example: "Chile")]
    public string $pais;
    #[OA\Property(property: "tipo", type: "string", example: "Local")]
    public string $tipo;
    #[OA\Property(property: "color", type: "string", example: "Rojo")]
    public string $color;
    #[OA\Property(property: "precio", type: "integer", example: 45000)]
    public int $precio;
    #[OA\Property(property: "precio_oferta", type: "integer", nullable: true, example: 35000)]
    public ?int $precio_oferta;
    #[OA\Property(property: "detalles", type: "string", nullable: true)]
    public ?string $detalles;
    #[OA\Property(property: "codigo_producto", type: "string", example: "SCL2025L")]
    public string $codigo_producto;
    #[OA\Property(property: "sizes_ids", type: "array", items: new OA\Items(type: "integer"), example: [1, 2, 3])]
    public array $sizes_ids;
}
