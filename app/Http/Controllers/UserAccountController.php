<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAccountController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/profile/export — exportación GDPR del usuario autenticado.
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $profile = Profile::with(['addresses.city', 'phones', 'documents'])
            ->where('user_id', $user->id)
            ->first();

        $payload = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'profile' => $profile,
            'addresses' => $profile?->addresses ?? [],
            'phones' => $profile?->phones ?? [],
            'documents' => $profile?->documents ?? [],
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->json($payload);
    }

    /**
     * GET /api/user/privacy-settings
     */
    public function getPrivacySettings(Request $request)
    {
        $prefs = $this->privacyPrefs($request);

        return $this->jsonSuccess([
            'profile_visibility' => $prefs['profile_visibility'] ?? true,
            'activity_visibility' => $prefs['activity_visibility'] ?? true,
            'marketing_emails' => $prefs['marketing_emails'] ?? true,
            'push_notifications' => $prefs['push_notifications'] ?? true,
        ]);
    }

    /**
     * PUT /api/user/privacy-settings
     */
    public function updatePrivacySettings(Request $request)
    {
        $validated = $request->validate([
            'profile_visibility' => 'sometimes|boolean',
            'activity_visibility' => 'sometimes|boolean',
            'marketing_emails' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
        ]);

        $profile = $this->profileForUser($request);
        if (! $profile) {
            return $this->jsonNotFound('Perfil no encontrado');
        }

        $prefs = array_merge($this->privacyPrefs($request), $validated);
        $profile->notification_preferences = $prefs;
        $profile->save();

        return $this->jsonSuccess($prefs, 'Configuración de privacidad actualizada.');
    }

    /**
     * DELETE /api/user/account — elimina la cuenta del usuario autenticado.
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->paymentMethods()->delete();

            $profile = Profile::where('user_id', $user->id)->first();
            if ($profile) {
                $profile->phones()->delete();
                $profile->documents()->delete();
                $profile->addresses()->delete();
                $profile->notifications()->delete();
                $profile->delete();
            }

            $user->roles()->detach();
            $user->delete();
        });

        return response()->json(null, 204);
    }

    private function profileForUser(Request $request): ?Profile
    {
        return Profile::where('user_id', $request->user()->id)->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function privacyPrefs(Request $request): array
    {
        $profile = $this->profileForUser($request);
        $stored = $profile?->notification_preferences;

        return is_array($stored) ? $stored : [];
    }
}
