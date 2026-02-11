<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Phone;
use App\Models\OperatorCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Listar todos los perfiles.
     */
    public function index()
    {
        $profiles = Profile::with(['user', 'addresses'])->get();
        return response()->json($profiles);
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
            'photo_users' => 'nullable|image|mimes:jpeg,png,jpg',
            'date_of_birth' => 'required|date',
            'maritalStatus' => 'required|in:married,divorced,single',
            'sex' => 'required|in:F,M',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

                // Verificar si ya existe un perfil para el usuario.
        $existingProfile = Profile::where('user_id', $request->user_id)->first();

        if ($existingProfile) {
            return response()->json([
                'message' => 'Ya existe un perfil asociado a este usuario.',
                'profile' => $existingProfile
            ], 409); // Código de estado HTTP 409: Conflicto
        }



        $profileData = $request->only([
            'user_id', 'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex'
        ]);

        // Establecer valores predeterminados para campos opcionales.
        $profileData['middleName'] = $request->middleName ?? '';
        $profileData['secondLastName'] = $request->secondLastName ?? '';
        $profileData['status'] = 'notverified'; // Estado inicial.

        // Manejar la carga de la imagen (required para delivery agent).
        if ($request->hasFile('photo_users')) {
            // Obtener la URL base según el entorno.
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');

            // Guardar la nueva imagen en el disco público.
            $path = $request->file('photo_users')->store('profile_images', 'public');
            $profileData['photo_users'] = $baseUrl . '/storage/' . $path; // Guarda la URL pública.
        }

        // Crear el perfil.
        $profile = Profile::create($profileData);

        return response()->json([
            'message' => 'Perfil creado exitosamente.',
            'profile' => $profile
        ], 201);
    }

    /**
     * Mostrar el perfil del usuario autenticado (GET /api/profile).
     */
    public function showCurrent(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
        $profile = Profile::with(['user', 'addresses'])->where('user_id', $user->id)->first();
        if (!$profile) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }
        return response()->json($profile);
    }

    /**
     * Mostrar un perfil específico por ID.
     */
    public function show($id = null)
    {
        if ($id === null || $id === '' || (is_string($id) && trim($id) === '')) {
            return response()->json(['message' => 'ID de perfil requerido'], 400);
        }
        $profile = Profile::with(['user', 'addresses'])->find($id);
        if (!$profile) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }
        return response()->json($profile);
    }
    public function update(Request $request, $id)
{
    // Buscar el perfil por ID o devolver error 404.
    $profile = Profile::findOrFail($id);

    // Validar los datos recibidos, incluyendo el formato correcto para la fecha.
    $validatedData = $request->validate([
        'firstName' => 'required|string|max:255',
        'middleName' => 'nullable|string|max:255',
        'lastName' => 'required|string|max:255',
        'secondLastName' => 'nullable|string|max:255',
        'photo_users' => 'nullable|image|mimes:jpeg,png,jpg',
        'date_of_birth' => 'required|date',
        'maritalStatus' => 'required|in:married,divorced,single',
        'sex' => 'required|in:F,M',
    ]);



    // Log para depurar la fecha recibida y asegurar que esté en el formato correcto
    Log::info('Fecha recibida: ' . $validatedData['date_of_birth']);  // Verificar que esté en formato Y-m-d

    // Obtener el nombre del perfil y la fecha de creación
    $created_at = $profile->created_at->format('YmdHis');  // Formato de fecha
    $date_of_birth = Carbon::parse($validatedData['date_of_birth'])->format('Ymd'); // Formato de fecha de nacimiento (Ymd)
    $firstName = $validatedData['firstName'];
    $lastName = $validatedData['lastName'];
    $randomDigits = strtoupper(substr(md5(mt_rand()), 0, 7));  // Generar 7 caracteres aleatorios

    // Establecer valores predeterminados para campos opcionales
    $validatedData['middleName'] = $request->middleName ?? '';  // Asegurar que 'middleName' no sea null
    $validatedData['secondLastName'] = $request->secondLastName ?? '';  // Asegurar que 'secondLastName' no sea null


    // Crear el nuevo nombre de la imagen
    $newImageName = "photo_users-{$created_at}-{$date_of_birth}-{$firstName}-{$lastName}-{$randomDigits}.jpg";

    // Obtener la URL base según el entorno
    $baseUrl = env('APP_ENV') === 'production'
        ? env('APP_URL_PRODUCTION')
        : env('APP_URL_LOCAL');

    // Mantener la URL de la foto anterior (si existe)
    $photo_usersxxx = $profile->photo_users;

    // Actualizar los campos del perfil
    $profile->fill($validatedData);

    // Manejo del archivo (si se sube uno nuevo)
    if ($request->hasFile('photo_users')) {
        // Eliminar la imagen anterior si existe
        if ($profile->photo_users) {
            // Log de la imagen anterior desde la base de datos
            Storage::disk('public')->delete(str_replace($baseUrl . '/storage/', '', $photo_usersxxx));
        } else {
            Log::info('No hay imagen anterior para eliminar.');
        }

        // Guardar la nueva imagen en el disco público
        $path = $request->file('photo_users')->storeAs('profile_images', $newImageName, 'public');
        $profile->photo_users = $baseUrl . '/storage/' . $path;
    }

    // Guardar los cambios en el perfil
    $profile->save();

    return response()->json([
        'message' => 'Perfil actualizado exitosamente.',
        'profile' => $profile,
        'isSuccess' => true
    ], 200);
}


    /**
     * Eliminar un perfil.
     */
    public function destroy($id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }

        // Eliminar la imagen asociada si existe.
        if ($profile->photo_users) {
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');
            Storage::disk('public')->delete(str_replace($baseUrl . '/storage/', '', $profile->photo_users));
        }

        $profile->delete();

        return response()->json(['message' => 'Perfil eliminado exitosamente']);
    }


// En tu controlador (UserController)
        public function getProfileId($id)
        {

            $profile = Profile::where('user_id', $id)->first();
            if ($profile) {
                return response()->json(['profileId' => $profile->id], 200);
            } else {
                return response()->json(['error' => 'User profile not found'], 404);
            }
        }

    /**
     * Crear un perfil de delivery agent.
     */
    public function createDeliveryAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'maritalStatus' => 'required|in:married,divorced,single',
            'sex' => 'required|in:F,M',
            'photo_users' => 'required|image|mimes:jpeg,png,jpg', // Required según modelo de negocio para DELIVERY
            'phone' => 'required|string|max:20', // Required según modelo de negocio
            'vehicle_type' => 'required|string|max:100', // Required según modelo de negocio
            'license_number' => 'required|string|max:255', // Required según modelo de negocio
            'company_id' => 'nullable|exists:delivery_companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Verificar si ya existe un perfil para el usuario
        $existingProfile = Profile::where('user_id', $request->user_id)->first();

        if ($existingProfile) {
            return response()->json([
                'message' => 'Ya existe un perfil asociado a este usuario.',
                'profile' => $existingProfile
            ], 409);
        }

        $profileData = $request->only([
            'user_id', 'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex'
        ]);

        $profileData['middleName'] = $request->middleName ?? '';
        $profileData['secondLastName'] = $request->secondLastName ?? '';
        $profileData['status'] = 'notverified';

        // Manejar la carga de la imagen (required para delivery agent).
        if ($request->hasFile('photo_users')) {
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');
            $path = $request->file('photo_users')->store('profile_images', 'public');
            $profileData['photo_users'] = $baseUrl . '/storage/' . $path;
        }

        // Crear el perfil
        $profile = Profile::create($profileData);

        // Registrar teléfono en tabla phones (una sola fuente de verdad)
        $this->createPhoneForProfile($profile, $request->phone);

        // Crear el delivery agent asociado
        $deliveryAgentData = [
            'profile_id' => $profile->id,
            'vehicle_type' => $request->vehicle_type, // Required según modelo de negocio
            'license_number' => $request->license_number, // Required según modelo de negocio
            'status' => 'activo',
            'working' => false,
        ];

        // Si se proporciona company_id, agregarlo
        if ($request->has('company_id') && $request->company_id) {
            $deliveryAgentData['company_id'] = $request->company_id;
        }

        $deliveryAgent = \App\Models\DeliveryAgent::create($deliveryAgentData);

        return response()->json([
            'success' => true,
            'message' => 'Delivery agent profile created successfully',
            'data' => [
                'profile' => $profile,
                'delivery_agent' => $deliveryAgent
            ]
        ], 201);
    }

    /**
     * Crear un perfil de commerce.
     */
    public function createCommerce(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'maritalStatus' => 'required|in:married,divorced,single',
            'sex' => 'required|in:F,M',
            'photo_users' => 'required|image|mimes:jpeg,png,jpg', // Required según modelo de negocio para COMMERCE
            'phone' => 'required|string|max:20', // Required según modelo de negocio
            'business_name' => 'required|string|max:255', // Required según modelo de negocio
            'business_type' => 'required|string|max:255', // Required según modelo de negocio
            'tax_id' => 'required|string|max:255', // Required según modelo de negocio
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
            'email' => 'nullable|email',
            'is_open' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Verificar si ya existe un perfil para el usuario
        $existingProfile = Profile::where('user_id', $request->user_id)->first();
        if ($existingProfile) {
            return response()->json([
                'message' => 'Ya existe un perfil asociado a este usuario.',
                'profile' => $existingProfile
            ], 409);
        }

        $profileData = $request->only([
            'user_id', 'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex'
        ]);
        $profileData['middleName'] = $request->middleName ?? '';
        $profileData['secondLastName'] = $request->secondLastName ?? '';
        $profileData['status'] = 'notverified';

        // Manejar la carga de la imagen (required para commerce).
        if ($request->hasFile('photo_users')) {
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');
            $path = $request->file('photo_users')->store('profile_images', 'public');
            $profileData['photo_users'] = $baseUrl . '/storage/' . $path;
        }

        // Crear el perfil
        $profile = Profile::create($profileData);

        // Registrar teléfono en tabla phones (una sola fuente de verdad)
        $this->createPhoneForProfile($profile, $request->phone);

        // Crear el commerce asociado.
        // IMPORTANTE: la dirección completa se gestiona en la tabla addresses
        // (addresses.role = 'commerce'); aquí no se persiste en la tabla commerces
        // para evitar duplicar información.
        $commerce = \App\Models\Commerce::create([
            'profile_id' => $profile->id,
            'business_name' => $request->business_name, // Required según modelo de negocio
            'business_type' => $request->business_type, // Required según modelo de negocio
            'tax_id' => $request->tax_id, // Required según modelo de negocio
            'description' => $request->description ?? null,
            'open' => $request->is_open ?? false,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $commerce->id,
                'business_name' => $commerce->business_name,
                'description' => $commerce->description,
                // La dirección principal del comercio se obtiene de addresses;
                // por compatibilidad, devolvemos la que llegó en la petición.
                'address' => $request->address,
                'phone' => $commerce->phone,
                'open' => $commerce->open,
                'mobile_payment_id' => null, // Agregado para el test
                'mobile_payment_bank' => null, // Agregado para el test
                'mobile_payment_phone' => null // Agregado para el test
            ]
        ], 201);
    }

    /**
     * Añadir comercio a un perfil ya existente (onboarding: perfil ya creado).
     * Devuelve el commerce creado con id para vincular la dirección del establecimiento.
     */
    public function addCommerceToProfile(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('addCommerceToProfile request', [
            'profile_id' => $request->input('profile_id'),
            'business_name' => $request->input('business_name'),
        ]);
        $profileId = $request->input('profile_id');
        if ($profileId !== null && is_numeric($profileId)) {
            $request->merge(['profile_id' => (int) $profileId]);
        }

        $validator = Validator::make($request->all(), [
            'profile_id' => 'required|integer|exists:profiles,id',
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'tax_id' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'open' => 'nullable|boolean',
            'schedule' => 'nullable|string|max:500',
            'owner_ci' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::warning('addCommerceToProfile validation failed', [
                'errors' => $validator->errors()->toArray(),
                'payload' => $request->only(['profile_id', 'business_name', 'tax_id']),
            ]);
            return response()->json([
                'message' => 'Datos no válidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $profile = Profile::findOrFail($request->profile_id);
            if ($profile->commerce) {
                return response()->json([
                    'message' => 'Este perfil ya tiene un comercio asociado.',
                    'data' => ['id' => $profile->commerce->id],
                ], 409);
            }

            $scheduleValue = null;
            if ($request->filled('schedule')) {
                $scheduleValue = is_array($request->schedule)
                    ? $request->schedule
                    : ['raw' => (string) $request->schedule];
            }

            $commerce = \App\Models\Commerce::create([
                'profile_id' => $profile->id,
                'business_name' => $request->business_name,
                'business_type' => $request->business_type,
                'tax_id' => $request->tax_id,
                'address' => $request->address,
                'open' => (bool) $request->input('open', false),
                'schedule' => $scheduleValue,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $commerce->id,
                    'business_name' => $commerce->business_name,
                    'address' => $commerce->address,
                    'open' => $commerce->open,
                ],
            ], 201);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('addCommerceToProfile: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Error al crear el comercio.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Crear un perfil de delivery company.
     */
    public function createDeliveryCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'maritalStatus' => 'required|in:married,divorced,single',
            'sex' => 'required|in:F,M',
            'photo_users' => 'required|image|mimes:jpeg,png,jpg', // Required según modelo de negocio para DELIVERY COMPANY
            'phone' => 'required|string|max:20', // Required según modelo de negocio
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'ci' => 'required|string|max:255', // tax_id required según modelo de negocio
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Verificar si ya existe un perfil para el usuario
        $existingProfile = Profile::where('user_id', $request->user_id)->first();

        if ($existingProfile) {
            return response()->json([
                'message' => 'Ya existe un perfil asociado a este usuario.',
                'profile' => $existingProfile
            ], 409);
        }

        $profileData = $request->only([
            'user_id', 'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex'
        ]);

        $profileData['middleName'] = $request->middleName ?? '';
        $profileData['secondLastName'] = $request->secondLastName ?? '';
        $profileData['status'] = 'notverified';

        // Manejar la carga de la imagen (required para delivery company).
        if ($request->hasFile('photo_users')) {
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');
            $path = $request->file('photo_users')->store('profile_images', 'public');
            $profileData['photo_users'] = $baseUrl . '/storage/' . $path;
        }

        // Crear el perfil
        $profile = Profile::create($profileData);

        // Registrar teléfono en tabla phones (una sola fuente de verdad)
        $this->createPhoneForProfile($profile, $request->phone);

        // Crear la delivery company asociada
        $deliveryCompany = \App\Models\DeliveryCompany::create([
            'profile_id' => $profile->id,
            'name' => $request->company_name,
            'tax_id' => $request->ci, // Required según modelo de negocio
            'address' => $request->address,
            'active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery company profile created successfully',
            'data' => [
                'profile' => $profile,
                'delivery_company' => $deliveryCompany
            ]
        ], 201);
    }

    /**
     * Registrar teléfono del perfil en tabla phones (una sola fuente de verdad para todos los roles).
     */
    private function createPhoneForProfile(Profile $profile, string $phoneString): void
    {
        $digits = preg_replace('/\D/', '', $phoneString);
        if (strlen($digits) < 7) {
            return;
        }
        $number = substr($digits, -7);
        $code4 = substr($digits, 0, 4);
        $code3 = ltrim($code4, '0');
        $operatorCode = OperatorCode::where('code', $code4)->orWhere('code', $code3)->first()
            ?? OperatorCode::first();
        if (!$operatorCode) {
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
