<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Email;
use App\Models\Profile;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    /**
     * Display a listing of the emails.
     */
    public function index()
    {
        // Obtener todos los correos electrónicos
        $emails = Email::with('profile')->where('status', true)->get();
        return response()->json($emails);
    }

    /**
     * Store a newly created email in storage.
     */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'profile_id' => 'required|exists:profiles,id',
    //         'email' => 'required|email|unique:emails,email',
    //         'is_primary' => 'boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     if ($request->is_primary) {
    //         Email::where('profile_id', $request->profile_id)
    //             ->where('is_primary', true)
    //             ->update(['is_primary' => false]);
    //     }

    //     $email = Email::create([
    //         'profile_id' => $request->profile_id,
    //         'email' => $request->email,
    //         'is_primary' => $request->is_primary ?? false,
    //     ]);

    //     return response()->json(['message' => 'Email created successfully', 'email' => $email], 201);
    // }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_id' => 'required|exists:profiles,user_id',
            'email' => 'required|email|unique:emails,email',
            'is_primary' => 'boolean',
        ], [
            'profile_id.required' => 'El ID del usuario es obligatorio.',
            'profile_id.exists' => 'El usuario especificado no tiene un perfil asociado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo debe ser válido.',
            'email.unique' => 'El correo ya está registrado.',
            'is_primary.boolean' => 'El campo "is_primary" debe ser un valor booleano.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Buscar el perfil asociado al user_id
        $profile = Profile::where('user_id', $request->profile_id)->firstOrFail();

        if ($request->is_primary) {
            Email::where('profile_id', $profile->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $email = Email::create([
            'profile_id' => $profile->id,
            'email' => $request->email,
            'is_primary' => $request->is_primary ?? false,
        ]);

        return response()->json(['message' => 'Email creado con éxito', 'email' => $email], 201);
    }



    /**
     * Display the specified email.
     */
    public function show($id)
    {
        $profile = Profile::where('user_id', $id)->firstOrFail();
        // Buscar el correo electrónico por ID y que esté activo
        $email = Email::with('profile')->where('profile_id', $profile->id)->where('status', true)->get();

        if (!$email) {
            return response()->json(['message' => 'Email not found or inactive'], 404);
        }

        return response()->json($email);
    }


    /**
     * Update the specified email in storage.
     */
    public function update(Request $request, $id)
    {
        // Buscar el correo específico por ID
        $email = Email::find($id);

        if (!$email) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // Validar que el campo 'is_primary' sea booleano
        $validator = Validator::make($request->all(), [
            'is_primary' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Si se establece como principal, marcar los demás correos del perfil como secundarios
        if ($request->is_primary) {
            Email::where('profile_id', $email->profile_id)
                ->where('id', '!=', $email->id) // Excluir el correo actual
                ->update(['is_primary' => false]);
        }

        // Actualizar el campo 'is_primary'
        $email->is_primary = $request->is_primary;
        $email->save();

        return response()->json([
            'message' => 'Email updated successfully',
            'email' => $email,
        ]);
    }

   /**
     * Remove the specified email from storage.
     */
    public function destroy($id)
    {
        $email = Email::find($id);

        if (!$email) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // Cambiar el estado del correo a inactivo
        $email->status = false;
        $email->save();

        return response()->json(['message' => 'Email marked as inactive']);
    }

}
