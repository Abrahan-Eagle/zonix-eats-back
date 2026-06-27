<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhoneRequest;
use App\Http\Requests\UpdatePhoneRequest;
use App\Models\OperatorCode;
use App\Models\Phone;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    private const MAX_PERSONAL = 5;

    private function getAuthProfile(): ?Profile
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        return Profile::where('user_id', $user->id)->first();
    }

    public function index(Request $request): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json(['success' => false, 'message' => 'Perfil no encontrado'], 404);
        }

        $query = Phone::with(['profile', 'operatorCode'])
            ->where('profile_id', $profile->id)
            ->where('status', true);

        if ($request->filled('context')) {
            $query->where('context', $request->input('context'));
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function getOperatorCodes(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => OperatorCode::all()]);
    }

    public function store(StorePhoneRequest $request): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json(['success' => false, 'message' => 'Perfil no encontrado'], 404);
        }

        $data = $request->validated();
        $context = $data['context'] ?? Phone::CONTEXT_PERSONAL;

        $count = Phone::where('profile_id', $profile->id)
            ->where('context', $context)
            ->where('status', true)
            ->count();
        if ($count >= self::MAX_PERSONAL) {
            return response()->json([
                'success' => false,
                'message' => 'Has alcanzado el máximo de teléfonos permitidos.',
            ], 422);
        }

        $phone = Phone::create([
            'profile_id' => $profile->id,
            'context' => $context,
            'operator_code_id' => $data['operator_code_id'],
            'number' => $data['number'],
            'is_primary' => $data['is_primary'] ?? false,
            'status' => true,
        ]);

        return response()->json(['success' => true, 'data' => $phone->load('operatorCode')], 201);
    }

    public function show(int $id): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json(['success' => false, 'message' => 'Perfil no encontrado'], 404);
        }

        $phone = Phone::with('operatorCode')
            ->where('profile_id', $profile->id)
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $phone]);
    }

    public function update(UpdatePhoneRequest $request, int $id): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json(['success' => false, 'message' => 'Perfil no encontrado'], 404);
        }

        $phone = Phone::where('profile_id', $profile->id)->findOrFail($id);
        $phone->update($request->validated());

        return response()->json(['success' => true, 'data' => $phone->fresh('operatorCode')]);
    }

    public function destroy(int $id): JsonResponse
    {
        $profile = $this->getAuthProfile();
        if (! $profile) {
            return response()->json(['success' => false, 'message' => 'Perfil no encontrado'], 404);
        }

        $phone = Phone::where('profile_id', $profile->id)->findOrFail($id);
        $phone->update(['status' => false, 'is_primary' => false]);

        return response()->json(['success' => true, 'message' => 'Teléfono desactivado']);
    }
}
