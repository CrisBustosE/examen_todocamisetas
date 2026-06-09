<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Size",
    title: "Size",
    description: "Modelo de Talla",
    required: ["nombre"]
)]
class SizeSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "nombre", type: "string", example: "XL")]
    public string $nombre;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;
}

#[OA\Schema(
    schema: "SizeInput",
    title: "Size Input",
    description: "Datos para crear o actualizar una talla",
    required: ["nombre"]
)]
class SizeInputSchema
{
    #[OA\Property(property: "nombre", type: "string", example: "XL")]
    public string $nombre;
}
