<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "TodoCamisetas API",
    description: "API REST para la gestión del inventario de TodoCamisetas. Incluye gestión de Clientes (Regulares/Preferenciales), Tallas y un sistema de cálculo dinámico de precios de Camisetas."
)]
#[OA\Server(
    url: "http://localhost:8080/api/v1",
    description: "Servidor Local Docker"
)]
#[OA\Tag(name: "Health", description: "Endpoint de observabilidad")]
#[OA\Tag(name: "Clientes", description: "Operaciones CRUD para los clientes")]
#[OA\Tag(name: "Tallas", description: "Operaciones CRUD para el catálogo de tallas")]
#[OA\Tag(name: "Camisetas", description: "Operaciones CRUD y cálculo de precio final")]
abstract class Controller
{
    //
}
