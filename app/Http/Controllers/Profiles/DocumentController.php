<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        return $request->user() && $request->user()->role === 'admin';
    }

    private function authProfile(Request $request): ?Profile
    {
        return Profile::where('user_id', $request->user()->id)->first();
    }

    private function canAccessProfile(Request $request, Profile $profile): bool
    {
        return $this->isAdmin($request) || ((int) $profile->user_id === (int) $request->user()->id);
    }

    public function index()
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        $profile = Profile::where('user_id', $user->id)->first();
        if (! $profile) {
            return response()->json([], 200);
        }
        $documents = Document::with('profile')
            ->where('profile_id', $profile->id)
            ->active()
            ->get();

        return response()->json($documents);
    }

    public function store(Request $request)
    {
        // Log::info('Datos recibidos:', $request->all());
        // Datos recibidos: {"profile_id":"3","type":"ci","issued_at":"2024-12-21T00:00:00.000","expires_at":"2024-12-21T00:00:00.000","number_ci":"94646464","front_image":{"Illuminate\\Http\\UploadedFile":"/tmp/php9hrbPi"}}

        // Solo se permiten CI y RIF
        if (! in_array($request->type, ['ci', 'rif'])) {
            return response()->json(['error' => 'Invalid document type. Only CI and RIF are allowed.'], 400);
        }

        $profile = Profile::find((int) $request->profile_id)
            ?? Profile::where('user_id', (int) $request->profile_id)->firstOrFail();
        if (! $this->canAccessProfile($request, $profile)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // CI y RIF son únicos por perfil (normativa Venezuela: un RIF/identificador por contribuyente).
        $existingDocument = Document::where('profile_id', $profile->id)
            ->where('type', $request->type)
            ->first();

        if ($existingDocument) {
            return response()->json(['error' => 'A document of type '.$request->type.' already exists for this profile.'], 400);
        }

        $validator = $this->getValidator($request->all(), $request->type);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $paths = $this->handleImageUpload($request);

        // Crear el documento con valores predeterminados
        $document = Document::create(array_merge(
            $request->only([
                'type', 'number_ci', 'rif_number', 'taxDomicile',
                'issued_at', 'expires_at',
            ]),
            $paths,
            [
                'profile_id' => $profile->id,
                'status' => true,
                'approved' => false,
            ]
        ));

        return response()->json(['message' => 'Document created successfully', 'document' => $document], 201);
    }

    public function show(Request $request, $id)
    {
        $profile = Profile::where('user_id', $id)->firstOrFail();
        if (! $this->canAccessProfile($request, $profile)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $document = Document::with('profile')
            ->where('profile_id', $profile->id)
            ->active()
            ->get();

        if ($document->isEmpty()) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // Log::info('+++++++++++++++++++++++++++++++++++ document===== :', ['document' => json_encode($document)]);

        return response()->json($document);
    }

    public function update(Request $request, $id)
    {
        $document = Document::find($id);

        if (! $document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $profile = Profile::find($document->profile_id);
        if (! $profile || ! $this->canAccessProfile($request, $profile)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $effectiveType = $request->type ?? $document->type;
        if (! in_array($effectiveType, ['ci', 'rif'])) {
            return response()->json(['error' => 'Invalid document type. Only CI and RIF are allowed.'], 400);
        }

        $payload = $request->all();
        $payload['profile_id'] = $request->profile_id ?? $document->profile_id;
        $payload['type'] = $effectiveType;
        $validator = $this->getValidator($payload, $effectiveType, true);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $paths = $this->handleImageUpload($request, $document);

        $document->update(array_merge(
            $request->only([
                'type', 'number_ci', 'rif_number', 'taxDomicile',
                'issued_at', 'expires_at', 'status',
            ]),
            $paths,
            [
                'profile_id' => $document->profile_id,
            ]
        ));

        return response()->json(['message' => 'Document updated successfully', 'document' => $document]);
    }

    public function destroy(Request $request, $id)
    {
        $document = Document::find($id);

        if (! $document) {
            return response()->json(['message' => 'Document not found'], 404);
        }
        $profile = Profile::find($document->profile_id);
        if (! $profile || ! $this->canAccessProfile($request, $profile)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $this->deleteImages($document);
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    private function getValidator(array $data, string $type, bool $isUpdate = false)
    {
        $rules = [
            'profile_id' => ($isUpdate ? 'sometimes' : 'required').'|exists:profiles,id',
            'issued_at' => ($isUpdate ? 'sometimes' : 'nullable').'|nullable|date',
            'expires_at' => ($isUpdate ? 'sometimes' : 'nullable').'|nullable|date|after_or_equal:issued_at',
            'status' => ($isUpdate ? 'sometimes' : 'nullable').'|boolean',
            'front_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];

        switch ($type) {
            case 'ci':
                $rules = array_merge($rules, [
                    'number_ci' => ($isUpdate ? 'sometimes' : 'required').'|integer|digits_between:6,9', // Venezuela: número cédula (solo dígitos, sin V)
                    'front_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                ]);
                break;
            case 'rif':
                $rules = array_merge($rules, [
                    'rif_number' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:20', 'regex:/^[VEJGP]-?\d{8}-?\d$/'], // Venezuela: X-NNNNNNNN-N (guiones opcionales)
                    'taxDomicile' => ($isUpdate ? 'sometimes' : 'nullable').'|nullable|string',
                    'front_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                ]);
                break;
            default:
                $rules['type'] = 'in:ci,rif';
                break;
        }

        return Validator::make($data, $rules);
    }

    private function handleImageUpload(Request $request, ?Document $document = null)
    {
        $paths = [];

        if ($request->hasFile('front_image')) {
            if ($document && $document->front_image) {
                Storage::disk('public')->delete($document->front_image);
            }
            $paths['front_image'] = $request->file('front_image')->store('documents/front', 'public');
        }

        return $paths;
    }

    private function deleteImages(Document $document)
    {
        if ($document->front_image) {
            Storage::disk('public')->delete($document->front_image);
        }

    }
}
