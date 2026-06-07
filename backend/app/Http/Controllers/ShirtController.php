<?php

namespace App\Http\Controllers;

use App\Models\Shirt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShirtController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Incluimos las relaciones para que el frontend vea de quién es y qué tallas tiene
            $shirts = Shirt::with(['client', 'sizes'])->get();
            return response()->json(['success' => true, 'data' => $shirts], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al listar', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'cliente_id'      => 'required|exists:clients,id',
                'titulo'          => 'required|string|max:255',
                'club'            => 'required|string|max:255',
                'pais'            => 'required|string|max:255',
                'tipo'            => 'required|string|max:255',
                'color'           => 'required|string|max:255',
                'precio'          => 'required|integer|min:0',
                'precio_oferta'   => 'nullable|integer|min:0',
                'detalles'        => 'nullable|string',
                'codigo_producto' => 'required|string|max:50|unique:shirts,codigo_producto,NULL,id,deleted_at,NULL',
                'sizes_ids'       => 'required|array',
                'sizes_ids.*'     => 'exists:sizes,id'
            ], [
                'cliente_id.required'          => 'El cliente es obligatorio.',
                'cliente_id.exists'            => 'El cliente especificado no existe.',
                'titulo.required'              => 'El título de la camiseta es obligatorio.',
                'titulo.max'                   => 'El título no puede superar los 255 caracteres.',
                'club.required'                => 'El club es obligatorio.',
                'pais.required'                => 'El país de origen es obligatorio.',
                'tipo.required'                => 'El tipo de camiseta es obligatorio.',
                'color.required'               => 'El color es obligatorio.',
                'precio.required'              => 'El precio es obligatorio.',
                'precio.integer'               => 'El precio debe ser un número entero.',
                'precio.min'                   => 'El precio no puede ser negativo.',
                'precio_oferta.integer'        => 'El precio de oferta debe ser un número entero.',
                'precio_oferta.min'            => 'El precio de oferta no puede ser negativo.',
                'codigo_producto.required'     => 'El código de producto es obligatorio.',
                'codigo_producto.unique'       => 'Este código de producto ya está registrado.',
                'codigo_producto.max'          => 'El código de producto no puede superar los 50 caracteres.',
                'sizes_ids.required'           => 'Debe seleccionar al menos una talla.',
                'sizes_ids.array'              => 'Las tallas deben enviarse como un arreglo.',
                'sizes_ids.*.exists'           => 'Una o más tallas seleccionadas no existen.',
            ]);

            // Verificar si existe como soft deleted y restaurar
            $existing = Shirt::onlyTrashed()->where('codigo_producto', $validated['codigo_producto'])->first();

            if ($existing) {
                $existing->restore();
                $existing->update(collect($validated)->except(['codigo_producto', 'sizes_ids'])->toArray());
                if (isset($validated['sizes_ids'])) {
                    $existing->sizes()->sync($validated['sizes_ids']);
                }
                DB::commit();
                return response()->json([
                    'success' => true,
                    'data'    => $existing->load('sizes'),
                    'message' => 'Camiseta restaurada y actualizada exitosamente.'
                ], 200);
            }

            // Creamos la camiseta (excluyendo el array de tallas del request masivo)
            $shirt = Shirt::create(collect($validated)->except('sizes_ids')->toArray());

            // Adjuntamos las tallas en la tabla pivote (Relación muchos a muchos)
            $shirt->sizes()->attach($validated['sizes_ids']);

            DB::commit();
            return response()->json(['success' => true, 'data' => $shirt->load('sizes'), 'message' => 'Camiseta creada exitosamente'], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear la camiseta', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            // El cliente debe enviar su ID por query string (ej: /api/v1/shirts/1?client_id=2)
            $clientId = $request->query('client_id');

            if (!$clientId) {
                return response()->json(['success' => false, 'message' => 'El parámetro client_id es obligatorio en la consulta.'], 400);
            }

            // Llamamos a la magia del modelo que hace el JOIN y calcula el precio
            $shirt = Shirt::findWithFinalPrice($id, $clientId);

            return response()->json(['success' => true, 'data' => $shirt], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Camiseta no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error de servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $shirt = Shirt::findOrFail($id);

            $validated = $request->validate([
                'cliente_id' => 'sometimes|required|exists:clients,id',
                'titulo' => 'sometimes|required|string|max:255',
                'club' => 'sometimes|required|string|max:255',
                'pais' => 'sometimes|required|string|max:255',
                'tipo' => 'sometimes|required|string|max:255',
                'color' => 'sometimes|required|string|max:255',
                'precio' => 'sometimes|required|integer|min:0',
                'precio_oferta' => 'nullable|integer|min:0',
                'detalles' => 'nullable|string',
                'codigo_producto' => 'sometimes|required|string|unique:shirts,codigo_producto,' . $id . '|max:50',
                'sizes_ids' => 'sometimes|required|array',
                'sizes_ids.*' => 'exists:sizes,id'
            ]);

            $shirt->update(collect($validated)->except('sizes_ids')->toArray());

            // Sincronizamos las tallas (borra las viejas que no estén en el array y agrega las nuevas)
            if (isset($validated['sizes_ids'])) {
                $shirt->sizes()->sync($validated['sizes_ids']);
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $shirt->load('sizes'), 'message' => 'Camiseta actualizada'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Camiseta no encontrada'], 404);
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
            $shirt = Shirt::findOrFail($id);
            // Gracias al onDelete('cascade') en la migración, se borra de la pivote automáticamente
            $shirt->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Camiseta eliminada exitosamente'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Camiseta no encontrada'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar', 'error' => $e->getMessage()], 500);
        }
    }

    // En caso de querer restaurar las camisetas (shirts) eliminadas
    /*
    public function restore(string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $shirt = Shirt::onlyTrashed()->findOrFail($id);
            $shirt->restore();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Camiseta restaurada exitosamente'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Camiseta no encontrada'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al restaurar', 'error' => $e->getMessage()], 500);
        }
    }
    */
}
