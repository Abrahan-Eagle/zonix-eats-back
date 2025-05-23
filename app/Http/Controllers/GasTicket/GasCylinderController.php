<?php

namespace App\Http\Controllers\GasTicket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GasCylinder;
use App\Models\GasSupplier;
use App\Models\Profile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class GasCylinderController extends Controller
{
    // Obtener todas las bombonas de gas
    public function index()
    {
        $cylinders = GasCylinder::all();
        return response()->json($cylinders);
    }


    public function store(Request $request)
    {

        //  Log::info('Datos recibidos:', $request->all());
        // local.INFO: Datos recibidos: {"gas_cylinder_code":"3434343434646494848454545484845454845484848484848","user_id":"3","company_supplier_id":"1","cylinder_type":"small","cylinder_weight":"10kg","manufacturing_date":"2024-12-20T00:00:00.000","photo_gas_cylinder":{"Illuminate\\Http\\UploadedFile":"/tmp/phpXWtBzg"}}

        // Validación de los datos de entrada.
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id', // Asegúrate de que el user_id existe
            'gas_cylinder_code' => 'required|string|max:255',
            'cylinder_type' => 'required|in:small,wide',
            'cylinder_weight' => 'required|in:10kg,18kg,45kg',
            'manufacturing_date' => 'required|date',
            'photo_gas_cylinder' => 'required|image|mimes:jpeg,png,jpg',
            'company_supplier_id' => 'required|exists:gas_suppliers,id', // Clave foránea a la tabla de proveedores de gas
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $profile = Profile::where('user_id', $request->user_id)->firstOrFail();

        // Preparar los datos para la creación de la bombona.
        $cylinderData = $request->only([
            'cylinder_type',
            'cylinder_weight', 'manufacturing_date', 'company_supplier_id'
        ]);

        // Cambiar user_id a profile_id
        $cylinderData['profile_id'] = $profile->id;

        // Formatear el código de la bombona
        // $currentDate = now()->format('Ymd'); // Formato de fecha YYYYMMDD
        $cylinderData['gas_cylinder_code'] = 'CYL-' . $request->input('gas_cylinder_code') . '-' . $request->input('user_id');

        $cylinderData['cylinder_quantity'] = 1; // Estado inicial de aprobación.
        $cylinderData['approved'] = 0; // Estado inicial de aprobación.

        // Manejar la carga de la imagen.
        if ($request->hasFile('photo_gas_cylinder')) {
            // Obtener la URL base según el entorno.
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');

            // Guardar la nueva imagen en el disco público.
            $path = $request->file('photo_gas_cylinder')->store('cylinder_images', 'public');
            $cylinderData['photo_gas_cylinder'] = $baseUrl . '/storage/' . $path; // Guarda la URL pública.
        }

        // Crear la bombona.
        $gasCylinder = GasCylinder::create($cylinderData);

        return response()->json([
            'message' => 'Bombona creada exitosamente.',
            'gasCylinder' => $gasCylinder
        ], 201);
    }



    public function getGasSuppliers()
    {
        $suppliers = GasSupplier::all(['id', 'name']); // Filtramos solo los campos necesarios
        return response()->json($suppliers, 200);
    }



    public function show($id)
    {
        // Validar que el ID sea un número válido
        if (!is_numeric($id)) {
            return response()->json(['message' => 'Invalid user ID'], 400);
        }

        try {
            // Buscar el perfil asociado al usuario con manejo automático de error
            $profile = Profile::where('user_id', $id)->firstOrFail();

            // Obtener las bombonas asociadas al perfil junto con el proveedor
            $cylinders = GasCylinder::with('gasSupplier')
                ->where('profile_id', $profile->id)
                ->get();

            // Verificar si hay bombonas encontradas
            if ($cylinders->isEmpty()) {
                return response()->json(['message' => 'No gas cylinders found'], 404);
            }

            // Retornar las bombonas en formato JSON
            return response()->json($cylinders);
        } catch (ModelNotFoundException $e) {
            // Manejar si no se encuentra el perfil
            return response()->json(['message' => 'Profile not found'], 404);
        } catch (\Exception $e) {
            // Manejar cualquier otro error
            return response()->json(['message' => 'An error occurred'], 500);
        }
    }

    // Actualizar una bombona de gas
    public function update(Request $request, $id)
    {
        $cylinder = GasCylinder::findOrFail($id);

        $validated = $request->validate([
            'capacity' => 'numeric',
            'available_quantity' => 'numeric',
        ]);

        $cylinder->update($validated);
        return response()->json($cylinder);
    }

    // Eliminar una bombona de gas
    public function destroy($id)
    {
        $cylinder = GasCylinder::findOrFail($id);
        $cylinder->delete();

        return response()->json(null, 204);
    }
}
