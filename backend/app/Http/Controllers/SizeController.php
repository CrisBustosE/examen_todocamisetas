<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use OpenApi\Attributes as OA;

class SizeController extends Controller
{
    #[OA\Get(
        path: "/sizes",
        operationId: "getSizes",
        summary: "Listar todas las tallas",
        tags: ["Tallas"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado exitoso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Size"))
                    ]
                )
            ),
            new OA\Response(response: 500, description: "Error interno del servidor")
        ]
    )]
    public function index(): JsonResponse
    {
        try {
            $sizes = Size::all();
            return response()->json(['success' => true, 'data' => $sizes], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener tallas', 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: "/sizes",
        operationId: "createSize",
        summary: "Crear una nueva talla",
        tags: ["Tallas"],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/SizeInput")),
        responses: [
            new OA\Response(response: 201, description: "Talla creada"),
            new OA\Response(response: 422, description: "Errores de validación"),
            new OA\Response(response: 500, description: "Error interno del servidor")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:50|unique:sizes,nombre,NULL,id,deleted_at,NULL',
            ], [
                'required' => 'El campo :attribute es obligatorio.',
                'string'   => 'El campo :attribute debe ser texto.',
                'unique'   => 'El valor de :attribute ya se encuentra registrado.',
                'max'      => 'El campo :attribute excede el largo permitido.',
            ]);

            // Verificar si existe como soft deleted y restaurar
            $existing = Size::onlyTrashed()->where('nombre', $validated['nombre'])->first();

            if ($existing) {
                $existing->restore();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'data' => $existing,
                    'message' => 'Talla restaurada exitosamente.'
                ], 200);
            }

            // Si no existe, crear normalmente
            $size = Size::create($validated);
            DB::commit();

            return response()->json(['success' => true, 'data' => $size, 'message' => 'Talla creada exitosamente'], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear la talla', 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: "/sizes/{id}",
        operationId: "getSize",
        summary: "Obtener talla por ID",
        tags: ["Tallas"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Talla encontrada"),
            new OA\Response(response: 404, description: "Talla no encontrada"),
            new OA\Response(response: 500, description: "Error interno del servidor")
        ]
    )]
    public function show(string $id): JsonResponse
    {
        try {
            $size = Size::findOrFail($id);
            return response()->json(['success' => true, 'data' => $size], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Talla no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error de servidor', 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Put(
        path: "/sizes/{id}",
        operationId: "updateSize",
        summary: "Actualizar una talla",
        tags: ["Tallas"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/SizeInput")),
        responses: [
            new OA\Response(response: 200, description: "Talla actualizada"),
            new OA\Response(response: 404, description: "Talla no encontrada"),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 500, description: "Error interno del servidor")
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $size = Size::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|unique:sizes,nombre,' . $id . '|max:50',
            ], [
                'required' => 'El campo :attribute es obligatorio.',
                'string'   => 'El campo :attribute debe ser texto.',
                'unique'   => 'El valor de :attribute ya se encuentra registrado.',
                'max'      => 'El campo :attribute excede el largo permitido.',
            ]);

            $size->update($validated);
            DB::commit();

            return response()->json(['success' => true, 'data' => $size, 'message' => 'Talla actualizada'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Talla no encontrada'], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar', 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/sizes/{id}",
        operationId: "deleteSize",
        summary: "Eliminar una talla",
        description: "Falla con 409 si la talla está asociada a una camiseta.",
        tags: ["Tallas"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Talla eliminada"),
            new OA\Response(response: 409, description: "Conflicto: talla en uso"),
            new OA\Response(response: 404, description: "Talla no encontrada"),
            new OA\Response(response: 500, description: "Error interno del servidor")
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $size = Size::findOrFail($id);

            // Verificamos si hay camisetas usando esta talla antes de borrarla
            if ($size->shirts()->exists()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'No se puede eliminar la talla porque hay camisetas asociadas a ella.'], 409);
            }

            $size->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Talla eliminada exitosamente'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Talla no encontrada'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar', 'error' => $e->getMessage()], 500);
        }
    }
}
