<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserPaymentMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserPaymentMethodController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $methods = $user->paymentMethods()->with('bank')->get();
        return response()->json(['success' => true, 'data' => $methods]);
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate([
                'type' => 'required|string',
                'bank_id' => 'nullable|exists:banks,id',
                'brand' => 'nullable|string',
                'account_number' => 'nullable|string',
                'phone' => 'nullable|string',
                'owner_name' => 'nullable|string',
                'owner_id' => 'nullable|string',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
            ]);
            $exists = $user->paymentMethods()->where('type', $data['type'])
                ->where('bank_id', $data['bank_id'] ?? null)
                ->where(function($q) use ($data) {
                    $q->where('account_number', $data['account_number'] ?? null)
                      ->orWhere('phone', $data['phone'] ?? null);
                })->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ya existe un método de pago igual registrado.'], 422);
            }
            $data['user_id'] = $user->id;
            $method = UserPaymentMethod::create($data);
            // Notificación: método de pago creado
            // Notification::send($user, new PaymentMethodCreated($method));
            return response()->json(['success' => true, 'data' => $method], 201);
        } catch (\Exception $e) {
            \Log::error('Error al crear método de pago de usuario: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al crear método de pago'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $method = $user->paymentMethods()->findOrFail($id);
            $data = $request->validate([
                'type' => 'sometimes|string',
                'bank_id' => 'nullable|exists:banks,id',
                'brand' => 'nullable|string',
                'account_number' => 'nullable|string',
                'phone' => 'nullable|string',
                'owner_name' => 'nullable|string',
                'owner_id' => 'nullable|string',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
            ]);
            $method->update($data);
            // Notificación: método de pago actualizado
            // Notification::send($user, new PaymentMethodUpdated($method));
            return response()->json(['success' => true, 'data' => $method]);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar método de pago de usuario: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al actualizar método de pago'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $method = $user->paymentMethods()->findOrFail($id);
            $method->delete();
            // Notificación: método de pago eliminado
            // Notification::send($user, new PaymentMethodDeleted($method));
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar método de pago de usuario: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al eliminar método de pago'], 500);
        }
    }
} 