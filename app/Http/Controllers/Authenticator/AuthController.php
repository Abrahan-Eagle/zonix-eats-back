<?php

namespace App\Http\Controllers\Authenticator;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Maneja la autenticación de usuario usando Google.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function googleUser(Request $request)
    {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'data' => 'required|array',
            'data.sub' => 'required|string',
            'data.name' => 'required|string',
            'data.given_name' => 'nullable|string',
            'data.family_name' => 'nullable|string',
            'data.picture' => 'nullable|url',
            'data.email' => 'required|email',
            'data.email_verified' => 'required|boolean',
        ]);

        // Si la validación falla, devolver un error
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Extraer los datos del request validado
            $validatedData = $validator->validated();
            $data = $validatedData['data'];
            $googleId = $data['sub'];
            $email = $data['email'];

            // Buscar o crear el usuario con la información proporcionada
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'google_id' => $googleId,
                    'given_name' => $data['given_name'],
                    'family_name' => $data['family_name'],
                    'profile_pic' => $data['picture'],
                    'completed_onboarding' => false, // Inicialmente false

                ]
            );

            // Crear el token de Sanctum
            $token = $user->createToken('GoogleToken')->plainTextToken;

            // Responder con los datos del usuario y el token
            return response()->json([
                'status' => true,
                'message' => 'User authenticated successfully',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_pic' => $user->profile_pic,
                    'completed_onboarding' => $user->completed_onboarding, // Incluir este campo
                ]
            ], 200);
        } catch (\Throwable $th) {
            // Manejo de errores en caso de excepciones
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Cierra la sesión del usuario autenticado.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Invalidar todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully'
        ]);
    }

    /**
     * Devuelve la información del usuario autenticado.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,  // Asegúrate de que el campo 'role' exista en la tabla users
            'google_id' => $user->google_id,
            'completed_onboarding' => $user->completed_onboarding,
        ]);
    }


     // Actualizar una user el campo  completed_onboarding
     public function update(Request $request, $id)
     {
         // Busca al usuario por ID y verifica si existe
         $user = User::find($id);

         if (!$user) {
             return response()->json(['error' => 'Usuario no encontrado'], 404);
         }

         // Valida el campo 'completed_onboarding' como booleano
         $validated = $request->validate([
             'completed_onboarding' => 'required|boolean',
         ]);

         // Actualiza el usuario con el campo validado
         $user->update($validated);

         return response()->json($user, 200);
     }


}
