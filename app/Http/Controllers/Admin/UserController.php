<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
 public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $role = $request->input('role');
        $status = $request->input('status');
        
        $query = User::with('profile');
        
        if ($role) {
            $query->where('role', $role);
        }
        
        // Si hay filtro de status, buscar en profile
        if ($status) {
            $query->whereHas('profile', function($q) use ($status) {
                $q->where('status', $status);
            });
        }
        
        $users = $query->paginate($perPage);
        
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::with(['profile', 'orders', 'commerce', 'deliveryAgent'])->findOrFail($id);
        return response()->json($user);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:active,suspended,banned',
        ]);

        $user = User::findOrFail($id);
        
        // Actualizar status en profile si existe
        if ($user->profile) {
            $user->profile->status = $request->status;
            $user->profile->save();
        }
        
        return response()->json([
            'message' => 'Estado del usuario actualizado',
            'user' => $user->load('profile')
        ]);
    }
    
    public function getUserActivity($id)
    {
        $user = User::findOrFail($id);
        
        // Obtener actividad del usuario (órdenes, logins, etc.)
        $activity = [
            'orders' => $user->orders()->orderBy('created_at', 'desc')->limit(10)->get(),
            'recent_logins' => [], // TODO: Implementar si hay tabla de logins
            'profile_updates' => [], // TODO: Implementar si hay auditoría
        ];
        
        return response()->json($activity);
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|in:users,commerce,delivery,admin,delivery_company,delivery_agent',
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Rol actualizado correctamente']);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}
