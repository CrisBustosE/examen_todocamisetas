<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: "/health",
        operationId: "healthCheck",
        summary: "Verificar estado del servicio",
        description: "Endpoint de observabilidad. Verifica que la API está disponible.",
        tags: ["Health"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Servicio operativo",
                content: new OA\JsonContent(
                    type: "object",
                    example: [
                        "status" => "online",
                        "service" => "TodoCamisetas API",
                        "version" => "1.0.0",
                        "timestamp" => "2026-05-21T10:30:00+00:00"
                    ]
                )
            )
        ]
    )]
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status'    => 'online',
            'service'   => 'TodoCamisetas API',
            'version'   => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
