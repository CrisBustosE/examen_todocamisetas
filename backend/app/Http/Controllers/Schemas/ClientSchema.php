<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Client",
    title: "Client",
    description: "Modelo completo del Cliente",
    required: ["nombre_comercial", "rut", "direccion", "categoria", "contacto_nombre", "contacto_correo"]
)]
class ClientSchema
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "nombre_comercial", type: "string", example: "Todo Deportes SpA")]
    public string $nombre_comercial;

    #[OA\Property(property: "rut", type: "string", example: "76123456-7")]
    public string $rut;

    #[OA\Property(property: "direccion", type: "string", example: "Av. Providencia 1234")]
    public string $direccion;

    #[OA\Property(property: "categoria", type: "string", enum: ["Regular", "Preferencial"], example: "Preferencial")]
    public string $categoria;

    #[OA\Property(property: "contacto_nombre", type: "string", example: "Juan Pérez")]
    public string $contacto_nombre;

    #[OA\Property(property: "contacto_correo", type: "string", format: "email", example: "juan@tododeportes.cl")]
    public string $contacto_correo;

    #[OA\Property(property: "porcentaje_oferta", type: "integer", nullable: true, example: 15)]
    public ?int $porcentaje_oferta;

    #[OA\Property(property: "created_at", type: "string", format: "date-time")]
    public string $created_at;

    #[OA\Property(property: "updated_at", type: "string", format: "date-time")]
    public string $updated_at;

    #[OA\Property(property: "deleted_at", type: "string", format: "date-time", nullable: true)]
    public ?string $deleted_at;
}

#[OA\Schema(
    schema: "ClientInput",
    title: "Client Input",
    description: "Datos para crear o actualizar un cliente",
    required: ["nombre_comercial", "rut", "direccion", "categoria", "contacto_nombre", "contacto_correo"]
)]
class ClientInputSchema
{
    #[OA\Property(property: "nombre_comercial", type: "string", example: "Todo Deportes SpA")]
    public string $nombre_comercial;

    #[OA\Property(property: "rut", type: "string", example: "76123456-7")]
    public string $rut;

    #[OA\Property(property: "direccion", type: "string", example: "Av. Providencia 1234")]
    public string $direccion;

    #[OA\Property(property: "categoria", type: "string", enum: ["Regular", "Preferencial"], example: "Preferencial")]
    public string $categoria;

    #[OA\Property(property: "contacto_nombre", type: "string", example: "Juan Pérez")]
    public string $contacto_nombre;

    #[OA\Property(property: "contacto_correo", type: "string", format: "email", example: "juan@tododeportes.cl")]
    public string $contacto_correo;

    #[OA\Property(property: "porcentaje_oferta", type: "integer", nullable: true, example: 15)]
    public ?int $porcentaje_oferta;
}
