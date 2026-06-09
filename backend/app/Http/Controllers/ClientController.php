<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    #[OA\Get(
        path: "/clients",
        operationId: "getClients",
        summary: "Listar todos los clientes",
        tags: ["Clientes"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado exitoso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Client"))
                    ]
                )
            )
        ]
    )]
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

    #[OA\Post(
        path: "/clients",
        operationId: "createClient",
        summary: "Registrar un nuevo cliente",
        tags: ["Clientes"],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/ClientInput")),
        responses: [
            new OA\Response(response: 201, description: "Cliente creado", content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", ref: "#/components/schemas/Client"),
                    new OA\Property(property: "message", type: "string")
                ]
            )),
            new OA\Response(response: 422, description: "Errores de validación")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        // 1. Iniciamos la transacción exigida por la rúbrica
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'nombre_comercial' => 'required|string|max:255',
                'rut'               => 'required|string|max:20|unique:clients,rut,NULL,id,deleted_at,NULL',
                'direccion'        => 'required|string|max:255',
                'categoria'        => 'required|in:Regular,Preferencial',
                'contacto_nombre'  => 'required|string|max:255',
                'contacto_correo' => 'required|email|unique:clients,contacto_correo|max:255',
                'porcentaje_oferta' => 'nullable|integer|min:0|max:100',
            ], [ // Mensajes Personalizados
                'nombre_comercial.required'  => 'El nombre comercial del cliente es obligatorio.',
                'nombre_comercial.max'       => 'El nombre comercial no puede superar los 255 caracteres.',
                'rut.required'               => 'El RUT es obligatorio.',
                'rut.unique'                 => 'Este RUT ya está registrado.',
                'rut.max'                    => 'El RUT no puede superar los 20 caracteres.',
                'direccion.required'         => 'La dirección es obligatoria.',
                'direccion.max'              => 'La dirección no puede superar los 255 caracteres.',
                'categoria.required'         => 'La categoría es obligatoria.',
                'categoria.in'               => 'La categoría debe ser Regular o Preferencial.',
                'contacto_nombre.required'   => 'El nombre de contacto es obligatorio.',
                'contacto_nombre.max'        => 'El nombre de contacto no puede superar los 255 caracteres.',
                'contacto_correo.required'   => 'El correo de contacto es obligatorio.',
                'contacto_correo.email'      => 'El correo de contacto debe ser una dirección válida.',
                'contacto_correo.unique'     => 'Este correo de contacto ya está registrado.',
                'contacto_correo.max'        => 'El correo no puede superar los 255 caracteres.',
                'porcentaje_oferta.integer'  => 'El porcentaje de oferta debe ser un número entero.',
                'porcentaje_oferta.min'      => 'El porcentaje de oferta no puede ser negativo.',
                'porcentaje_oferta.max'      => 'El porcentaje de oferta no puede superar el 100%.',
            ]);

            // Verificar si existe como soft deleted y restaurar
            $existing = Client::onlyTrashed()->where('rut', $validated['rut'])->first();

            if ($existing) {
                $existing->restore();
                $existing->update(collect($validated)->except('rut')->toArray());
                DB::commit();
                return response()->json([
                    'success' => true,
                    'data'    => $existing->fresh(),
                    'message' => 'Cliente restaurado y actualizado exitosamente.'
                ], 200);
            }

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

    #[OA\Get(
        path: "/clients/{id}",
        operationId: "getClient",
        summary: "Obtener cliente por ID",
        tags: ["Clientes"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Cliente encontrado", content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", ref: "#/components/schemas/Client")
                ]
            )),
            new OA\Response(response: 404, description: "Cliente no encontrado")
        ]
    )]
    public function show(string $id): JsonResponse
    {
        try {
            $client = Client::findOrFail($id);
            return response()->json(['success' => true, 'data' => $client], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error de servidor', 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Put(
        path: "/clients/{id}",
        operationId: "updateClient",
        summary: "Actualizar un cliente",
        tags: ["Clientes"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/ClientInput")),
        responses: [
            new OA\Response(response: 200, description: "Cliente actualizado"),
            new OA\Response(response: 404, description: "Cliente no encontrado"),
            new OA\Response(response: 422, description: "Errores de validación")
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $client = Client::findOrFail($id);

            $validated = $request->validate([
                'nombre_comercial' => 'sometimes|required|string|max:255',
                'rut' => 'sometimes|required|string|unique:clients,rut,' . $id . '|max:20',
                'direccion' => 'sometimes|required|string|max:255',
                'categoria' => 'sometimes|required|in:Regular,Preferencial',
                'contacto_nombre' => 'sometimes|required|string|max:255',
                'contacto_correo' => 'sometimes|required|email|unique:clients,contacto_correo,'.$id.'|max:255',
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

    #[OA\Delete(
        path: "/clients/{id}",
        operationId: "deleteClient",
        summary: "Eliminar un cliente",
        description: "Aplica un SoftDelete. Falla si el cliente tiene camisetas asociadas.",
        tags: ["Clientes"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Cliente eliminado exitosamente"),
            new OA\Response(response: 409, description: "Conflicto: tiene camisetas asociadas"),
            new OA\Response(response: 404, description: "Cliente no encontrado")
        ]
    )]
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
