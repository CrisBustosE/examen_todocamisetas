<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SizeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $sizes = Size::all();
            return response()->json(['success' => true, 'data' => $sizes], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener tallas', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:50|unique:sizes,nombre,NULL,id,deleted_at,NULL',
            ], [
                'nombre.required' => 'El nombre de la talla es obligatorio.',
                'nombre.string'   => 'El nombre de la talla debe ser texto.',
                'nombre.unique'   => 'Esta talla ya está registrada.',
                'nombre.max'      => 'El nombre de la talla no puede superar los 50 caracteres.',
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

    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $size = Size::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|unique:sizes,nombre,' . $id . '|max:50',
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
