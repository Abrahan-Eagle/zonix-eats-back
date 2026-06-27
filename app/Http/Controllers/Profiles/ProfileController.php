<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\OperatorCode;
use App\Models\Phone;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use ApiResponse;

    private function isAdmin(Request $request): bool
    {
        return $request->user() && $request->user()->role === 'admin';
    }

    private function canAccessProfile(Request $request, Profile $profile): bool
    {
        return $this->isAdmin($request) || ((int) $profile->user_id === (int) $request->user()->id);
    }

    /**
     * Listar todos los perfiles.
     */
    public function index(Request $request)
    {
        if ($this->isAdmin($request)) {
            $profiles = Profile::with(['user', 'addresses'])->get();

            return $this->jsonSuccess($profiles);
        }

        $profile = Profile::with(['user', 'addresses'])
            ->where('user_id', $request->user()->id)
            ->first();

        return $this->jsonSuccess($profile ? [$profile] : []);
    }

    /**
     * Crear un nuevo perfil.
     */
    public function store(Request $request)
    {
        // Validación de los datos de entrada.
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'required|string|max:255',
            'secondLastName' => 'nullable|string|max:255',
            'photo_users' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'date_of_birth' => 'required|date',
            'maritalStatus' => 'required|in:married,divorced,single',
            'sex' => 'required|in:F,M',
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Error de validación', 400, 'VALIDATION_ERROR', $validator->errors());
        }

        if (! $this->isAdmin($request) && (int) $request->user_id !== (int) $request->user()->id) {
            return $this->jsonForbidden('No autorizado');
        }

        // Verificar si ya existe un perfil para el usuario.
        $existingProfile = Profile::where('user_id', $request->user_id)->first();

        if ($existingProfile) {
            return $this->jsonError(
                'Ya existe un perfil asociado a este usuario.',
                409,
                'PROFILE_ALREADY_EXISTS',
                null,
                ['profile' => $existingProfile]
            );
        }

        $profileData = $request->only([
            'user_id', 'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex',
        ]);

        // Establecer valores predeterminados para campos opcionales.
        $profileData['middleName'] = $request->middleName ?? '';
        $profileData['secondLastName'] = $request->secondLastName ?? '';
        $profileData['status'] = 'notverified'; // Estado inicial.

        // Manejar la carga de la imagen de perfil.
        if ($request->hasFile('photo_users')) {
            // Obtener la URL base según el entorno.
            $baseUrl = config('app.env') === 'production'
                ? config('app.url_production')
                : config('app.url_local');

            // Guardar la nueva imagen en el disco público.
            $path = $request->file('photo_users')->store('profile_images', 'public');
            $profileData['photo_users'] = $baseUrl.'/storage/'.$path; // Guarda la URL pública.
        }

        // Crear el perfil.
        $profile = Profile::create($profileData);

        return $this->jsonSuccess(['profile' => $profile], 'Perfil creado exitosamente.', 201);
    }

    /**
     * Mostrar el perfil del usuario autenticado (GET /api/profile).
     */
    public function showCurrent(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return $this->jsonUnauthorized();
        }
        $profile = Profile::with(['user', 'addresses'])->where('user_id', $user->id)->first();
        if (! $profile) {
            return $this->jsonNotFound('Perfil no encontrado');
        }

        return $this->jsonSuccess($profile);
    }

    /**
     * Mostrar un perfil específico por ID.
     */
    public function show(Request $request, $id = null)
    {
        if ($id === null || $id === '' || (is_string($id) && trim($id) === '')) {
            return $this->jsonError('ID de perfil requerido', 400, 'PROFILE_ID_REQUIRED');
        }
        $profile = Profile::with(['user', 'addresses'])->find($id);
        if (! $profile) {
            return $this->jsonNotFound('Perfil no encontrado');
        }
        if (! $this->canAccessProfile($request, $profile)) {
            return $this->jsonForbidden();
        }

        return $this->jsonSuccess($profile);
    }

    /**
     * PUT /api/profile — actualizar el perfil del usuario autenticado.
     */
    public function updateCurrent(Request $request)
    {
        $user = $request->user();
        $profile = Profile::where('user_id', $user->id)->first();
        if (! $profile) {
            return $this->jsonNotFound('Perfil no encontrado');
        }

        return $this->update($request, $profile->id);
    }

    public function update(Request $request, $id)
    {
        // Buscar el perfil por ID o devolver error 404.
        $profile = Profile::findOrFail($id);
        if (! $this->canAccessProfile($request, $profile)) {
            return $this->jsonForbidden();
        }

        // Validar los datos recibidos (date_of_birth nullable para perfiles sin fecha).
        $validatedData = $request->validate([
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'required|string|max:255',
            'secondLastName' => 'nullable|string|max:255',
            'photo_users' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'date_of_birth' => 'nullable|date',
            'maritalStatus' => 'required|in:married,divorced,single',
            'sex' => 'required|in:F,M',
        ]);

        // Si no se envía date_of_birth, mantener la existente o usar valor por defecto para nombre de imagen
        if (empty($validatedData['date_of_birth'])) {
            $validatedData['date_of_birth'] = $profile->date_of_birth
                ? $profile->date_of_birth->format('Y-m-d')
                : '2000-01-01';
        }

        // Log para depurar la fecha recibida
        Log::debug('Fecha recibida: '.$validatedData['date_of_birth']);

        // Obtener el nombre del perfil y la fecha de creación
        $created_at = $profile->created_at->format('YmdHis');
        $date_of_birth = Carbon::parse($validatedData['date_of_birth'])->format('Ymd');
        $firstName = $validatedData['firstName'];
        $lastName = $validatedData['lastName'];
        $randomDigits = strtoupper(substr(md5(mt_rand()), 0, 7));  // Generar 7 caracteres aleatorios

        // Establecer valores predeterminados para campos opcionales
        $validatedData['middleName'] = $request->middleName ?? '';  // Asegurar que 'middleName' no sea null
        $validatedData['secondLastName'] = $request->secondLastName ?? '';  // Asegurar que 'secondLastName' no sea null

        // Crear el nuevo nombre de la imagen
        $newImageName = "photo_users-{$created_at}-{$date_of_birth}-{$firstName}-{$lastName}-{$randomDigits}.jpg";

        // Obtener la URL base según el entorno
        $baseUrl = config('app.env') === 'production'
            ? config('app.url_production')
            : config('app.url_local');

        // Mantener la URL de la foto anterior (si existe)
        $photoUsersPath = $profile->photo_users;

        // Actualizar los campos del perfil
        $profile->fill($validatedData);

        // Manejo del archivo (si se sube uno nuevo)
        if ($request->hasFile('photo_users')) {
            // Eliminar la imagen anterior si existe
            if ($profile->photo_users) {
                // Log de la imagen anterior desde la base de datos
                Storage::disk('public')->delete(str_replace($baseUrl.'/storage/', '', $photoUsersPath));
            } else {
                Log::info('No hay imagen anterior para eliminar.');
            }

            // Guardar la nueva imagen en el disco público
            $path = $request->file('photo_users')->storeAs('profile_images', $newImageName, 'public');
            $profile->photo_users = $baseUrl.'/storage/'.$path;
        }

        // Guardar los cambios en el perfil
        $profile->save();

        return $this->jsonSuccess([
            'profile' => $profile,
            'isSuccess' => true,
        ], 'Perfil actualizado exitosamente.');
    }

    /**
     * Eliminar un perfil.
     */
    public function destroy(Request $request, $id)
    {
        $profile = Profile::find($id);

        if (! $profile) {
            return $this->jsonNotFound('Perfil no encontrado');
        }
        if (! $this->canAccessProfile($request, $profile)) {
            return $this->jsonForbidden();
        }

        // Eliminar la imagen asociada si existe.
        if ($profile->photo_users) {
            $baseUrl = config('app.env') === 'production'
                ? config('app.url_production')
                : config('app.url_local');
            Storage::disk('public')->delete(str_replace($baseUrl.'/storage/', '', $profile->photo_users));
        }

        $profile->delete();

        return $this->jsonSuccess(null, 'Perfil eliminado exitosamente.');
    }

    public function getProfileId($id)
    {
        $profile = Profile::where('user_id', $id)->first();
        if ($profile) {
            return $this->jsonSuccess(['profileId' => $profile->id]);
        }

        return $this->jsonNotFound('User profile not found');
    }

    /**
     * Registrar teléfono del perfil en tabla phones.
     */
    private function createPhoneForProfile(Profile $profile, string $phoneString): void
    {
        $digits = preg_replace('/\D/', '', $phoneString);
        if (strlen($digits) < 7) {
            Log::warning("createPhoneForProfile: teléfono '{$phoneString}' tiene menos de 7 dígitos, no se creó registro.", [
                'profile_id' => $profile->id,
                'digits_count' => strlen($digits),
            ]);

            return;
        }
        $number = substr($digits, -7);
        $code4 = substr($digits, 0, 4);
        $code3 = ltrim($code4, '0');
        $operatorCode = OperatorCode::where('code', $code4)->orWhere('code', $code3)->first()
            ?? OperatorCode::first();
        if (! $operatorCode) {
            Log::warning("createPhoneForProfile: no se encontró código de operador para '{$phoneString}'.", [
                'profile_id' => $profile->id,
            ]);

            return;
        }
        Phone::create([
            'profile_id' => $profile->id,
            'operator_code_id' => $operatorCode->id,
            'number' => $number,
            'is_primary' => true,
            'status' => true,
        ]);
    }
}
