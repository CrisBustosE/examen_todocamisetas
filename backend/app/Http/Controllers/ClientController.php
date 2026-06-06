<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $clients = Client::all();
            return response()->json(['success' => true, 'data' => $clients], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        // 1. Iniciamos la transacción exigida por la rúbrica
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'nombre_comercial' => 'required|string|max:255',
                'rut' => 'required|string|unique:clients,rut|max:20',
                'direccion' => 'required|string|max:255',
                'categoria' => 'required|in:Regular,Preferencial',
                'contacto_nombre' => 'required|string|max:255',
                'contacto_correo' => 'required|email|max:255',
                'porcentaje_oferta' => 'nullable|integer|min:0|max:100',
            ],[
                // Mensajes personalizados
                'nombre_comercial.required' => 'El nombre del cliente es obligatorio.',
                'rut.required' => 'El RUT es obligatorio.',
                'rut.unique' => 'Este RUT ya está registrado.',
                'direccion.required' => 'La dirección es obligatoria.',
                'categoria.required' => 'La categoria es obligatoria.',
                'contacto_nombre.required' => 'El nombre de contacto es obligatorio.',
                'contacto_correo.required' => 'El correo de contacto es obligatorio.'
            ]);

            $client = Client::create($validated);

            // 2. Si todo salió bien, guardamos los cambios (Commit)
            DB::commit();

            return response()->json(['success' => true, 'data' => $client, 'message' => 'Cliente creado exitosamente'], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // 3. Si explotó algo, echamos todo para atrás (Rollback)
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear el cliente', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $client = Client::findOrFail($id);
            return response()->json(['success' => true, 'data' => $client], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Client no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error de servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $client = Client::findOrFail($id);

            $validated = $request->validate([
                'nombre_comercial' => 'sometimes|required|string|max:255',
                'rut' => 'sometimes|required|string|unique:clients,rut,'.$id.'|max:20',
                'direccion' => 'sometimes|required|string|max:255',
                'categoria' => 'sometimes|required|in:Regular,Preferencial',
                'contacto_nombre' => 'sometimes|required|string|max:255',
                'contacto_correo' => 'sometimes|required|email|max:255',
                'porcentaje_oferta' => 'nullable|integer|min:0|max:100',
            ]);

            $client->update($validated);
            DB::commit();

            return response()->json(['success' => true, 'data' => $client, 'message' => 'Cliente actualizado'], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
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
            $client = Client::findOrFail($id);

            // La rúbrica pide validar que no se pueda borrar un cliente con camisetas asociadas
            if ($client->shirts()->exists()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'No se puede eliminar el cliente porque tiene camisetas asociadas.'], 409);
            }

            $client->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Cliente eliminado exitosamente'], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar', 'error' => $e->getMessage()], 500);
        }
    }
}
