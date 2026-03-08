<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhoneRequest;
use App\Http\Requests\UpdatePhoneRequest;
use App\Models\Commerce;
use App\Models\DeliveryCompany;
use App\Models\OperatorCode;
use App\Models\Phone;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    /** Máximo de teléfonos personales activos por perfil. */
    private const MAX_PERSONAL = 5;

    /** Máximo de teléfonos por comercio (por commerce_id). */
    private const MAX_PER_COMMERCE = 4;

    /** Máximo de teléfonos por empresa de delivery (por delivery_company_id). */
    private const MAX_PER_DELIVERY_COMPANY = 4;

    /** Máximo de teléfonos con contexto admin por perfil. */
    private const MAX_ADMIN = 1;

    /**
     * Obtener el perfil del usuario autenticado.
     */
    private function getAuthProfile(): ?Profile
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        return Profile::where('user_id', $user->id)->first();
    }

    /**
     * Listar teléfonos del usuario autenticado.
     * Query: context (personal|commerce|delivery_company|admin), commerce_id, delivery_company_id.
     */
    public function index(Request $request): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado',
            ], 404);
        }

        $query = Phone::with(['profile', 'operatorCode', 'commerce', 'deliveryCompany'])
            ->where('profile_id', $profile->id)
            ->where('status', true);

        if ($request->filled('context')) {
            $query->where('context', $request->input('context'));
        }
        if ($request->filled('commerce_id')) {
            $query->where('commerce_id', (int) $request->input('commerce_id'));
        }
        if ($request->filled('delivery_company_id')) {
            $query->where('delivery_company_id', (int) $request->input('delivery_company_id'));
        }

        $phones = $query->get();

        return response()->json([
            'success' => true,
            'data' => $phones,
        ]);
    }

    /**
     * Códigos de operador para dropdown (público para usuarios autenticados).
     */
    public function getOperatorCodes(): JsonResponse
    {
        $operatorCodes = OperatorCode::all();

        return response()->json([
            'success' => true,
            'data' => $operatorCodes,
        ]);
    }

    /**
     * Crear teléfono en el perfil del usuario autenticado.
     * context obligatorio; commerce_id/delivery_company_id según contexto. Se valida propiedad del comercio/empresa.
     */
    public function store(StorePhoneRequest $request): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado',
            ], 404);
        }

        $context = $request->validated()['context'] ?? Phone::CONTEXT_PERSONAL;
        $commerceId = $request->validated()['commerce_id'] ?? null;
        $deliveryCompanyId = $request->validated()['delivery_company_id'] ?? null;

        if (in_array($context, [Phone::CONTEXT_PERSONAL, Phone::CONTEXT_ADMIN], true)) {
            $commerceId = null;
            $deliveryCompanyId = null;
        }

        if ($context === Phone::CONTEXT_COMMERCE && $commerceId) {
            $owned = Commerce::where('id', $commerceId)->where('profile_id', $profile->id)->exists();
            if (! $owned) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para agregar teléfonos a este comercio.',
                ], 403);
            }
            $count = Phone::where('profile_id', $profile->id)
                ->where('context', Phone::CONTEXT_COMMERCE)
                ->where('commerce_id', $commerceId)
                ->where('status', true)
                ->count();
            if ($count >= self::MAX_PER_COMMERCE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Has alcanzado el máximo de ' . self::MAX_PER_COMMERCE . ' teléfonos para este comercio.',
                ], 422);
            }
        }

        if ($context === Phone::CONTEXT_DELIVERY_COMPANY && $deliveryCompanyId) {
            $owned = DeliveryCompany::where('id', $deliveryCompanyId)->where('profile_id', $profile->id)->exists();
            if (! $owned) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para agregar teléfonos a esta empresa.',
                ], 403);
            }
            $count = Phone::where('profile_id', $profile->id)
                ->where('context', Phone::CONTEXT_DELIVERY_COMPANY)
                ->where('delivery_company_id', $deliveryCompanyId)
                ->where('status', true)
                ->count();
            if ($count >= self::MAX_PER_DELIVERY_COMPANY) {
                return response()->json([
                    'success' => false,
                    'message' => 'Has alcanzado el máximo de ' . self::MAX_PER_DELIVERY_COMPANY . ' teléfonos para esta empresa.',
                ], 422);
            }
        }

        if ($context === Phone::CONTEXT_PERSONAL) {
            $count = Phone::where('profile_id', $profile->id)
                ->where('context', Phone::CONTEXT_PERSONAL)
                ->where('status', true)
                ->count();
            if ($count >= self::MAX_PERSONAL) {
                return response()->json([
                    'success' => false,
                    'message' => 'Has alcanzado el máximo de ' . self::MAX_PERSONAL . ' teléfonos personales.',
                ], 422);
            }
        }

        if ($context === Phone::CONTEXT_ADMIN) {
            $count = Phone::where('profile_id', $profile->id)
                ->where('context', Phone::CONTEXT_ADMIN)
                ->where('status', true)
                ->count();
            if ($count >= self::MAX_ADMIN) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes tener un teléfono de contacto admin.',
                ], 422);
            }
        }

        $number = $request->validated()['number'];
        $operatorCodeId = (int) $request->validated()['operator_code_id'];

        $exists = Phone::where('operator_code_id', $operatorCodeId)
            ->where('number', $number)
            ->where('status', true)
            ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Este número ya está registrado.',
                'errors' => ['number' => ['El número con ese operador ya está en uso.']],
            ], 422);
        }

        $phone = Phone::create([
            'profile_id' => $profile->id,
            'context' => $context,
            'commerce_id' => $commerceId,
            'delivery_company_id' => $deliveryCompanyId,
            'operator_code_id' => $operatorCodeId,
            'number' => $number,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        $phone->load(['profile', 'operatorCode', 'commerce', 'deliveryCompany']);

        return response()->json([
            'success' => true,
            'data' => $phone,
            'message' => 'Teléfono creado correctamente',
        ], 201);
    }

    /**
     * Listar teléfonos por user_id. Solo el mismo usuario o admin pueden ver.
     */
    public function phonesByUserId(int $userId): JsonResponse
    {
        $authUser = auth()->user();
        if (! $authUser) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }
        if ($authUser->id !== $userId && $authUser->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $profile = Profile::where('user_id', $userId)->first();
        if (! $profile) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $phones = Phone::with(['profile', 'operatorCode', 'commerce', 'deliveryCompany'])
            ->where('profile_id', $profile->id)
            ->where('status', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $phones,
        ]);
    }

    /**
     * Mostrar un teléfono por id. Solo si pertenece al perfil del usuario autenticado.
     */
    public function show(int $id): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado',
            ], 404);
        }

        $phone = Phone::with(['profile', 'operatorCode', 'commerce', 'deliveryCompany'])
            ->where('id', $id)
            ->where('profile_id', $profile->id)
            ->first();

        if (! $phone) {
            return response()->json([
                'success' => false,
                'message' => 'Teléfono no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $phone,
        ]);
    }

    /**
     * Actualizar teléfono. Solo si pertenece al perfil del usuario autenticado.
     */
    public function update(UpdatePhoneRequest $request, int $id): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado',
            ], 404);
        }

        $phone = Phone::where('id', $id)->where('profile_id', $profile->id)->first();
        if (! $phone) {
            return response()->json([
                'success' => false,
                'message' => 'Teléfono no encontrado',
            ], 404);
        }

        $data = $request->validated();

        if (isset($data['context']) && in_array($data['context'], [Phone::CONTEXT_PERSONAL, Phone::CONTEXT_ADMIN], true)) {
            $data['commerce_id'] = null;
            $data['delivery_company_id'] = null;
        }
        if (isset($data['commerce_id']) && $data['context'] === Phone::CONTEXT_COMMERCE) {
            $owned = Commerce::where('id', $data['commerce_id'])->where('profile_id', $profile->id)->exists();
            if (! $owned) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para asignar este comercio.',
                ], 403);
            }
        }
        if (isset($data['delivery_company_id']) && $data['context'] === Phone::CONTEXT_DELIVERY_COMPANY) {
            $owned = DeliveryCompany::where('id', $data['delivery_company_id'])->where('profile_id', $profile->id)->exists();
            if (! $owned) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para asignar esta empresa.',
                ], 403);
            }
        }

        if (isset($data['number']) && isset($data['operator_code_id'])) {
            $exists = Phone::where('operator_code_id', (int) $data['operator_code_id'])
                ->where('number', $data['number'])
                ->where('status', true)
                ->where('id', '!=', $id)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este número ya está registrado.',
                    'errors' => ['number' => ['El número con ese operador ya está en uso.']],
                ], 422);
            }
        }

        $phone->fill($data);
        $phone->save();
        $phone->load(['profile', 'operatorCode', 'commerce', 'deliveryCompany']);

        return response()->json([
            'success' => true,
            'data' => $phone,
            'message' => 'Teléfono actualizado correctamente',
        ]);
    }

    /**
     * Desactivar teléfono (soft delete). Solo si pertenece al perfil del usuario autenticado.
     */
    public function destroy(int $id): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado',
            ], 404);
        }

        $phone = Phone::where('id', $id)->where('profile_id', $profile->id)->first();
        if (! $phone) {
            return response()->json([
                'success' => false,
                'message' => 'Teléfono no encontrado',
            ], 404);
        }

        $phone->status = false;
        $phone->save();

        return response()->json([
            'success' => true,
            'message' => 'Teléfono eliminado correctamente',
        ]);
    }
}
