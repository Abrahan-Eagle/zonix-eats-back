<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Obtener direcciones del usuario
     */
    public function getUserAddresses(): JsonResponse
    {
        try {
            $profile = auth()->user()->profile;

            $addresses = Address::with('city')
                ->where('profile_id', $profile->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $addressesData = $addresses->map(function ($address) {
                $cityName = $address->relationLoaded('city') && $address->city ? $address->city->name : null;

                return [
                    'id' => $address->id,
                    'name' => $address->street ?? null,
                    'address_line_1' => $address->street,
                    'address_line_2' => $address->house_number,
                    'city' => $cityName,
                    'state' => null,
                    'postal_code' => $address->postal_code,
                    'country' => null,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'is_default' => $address->is_default,
                    'delivery_instructions' => null,
                    'formatted_address' => $this->formatAddress($address),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $addressesData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting user addresses: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las direcciones',
            ], 500);
        }
    }

    /**
     * Crear nueva dirección
     */
    public function createAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:100',
            'street' => 'required_without:address_line_1|string|max:255',
            'address_line_1' => 'required_without:street|string|max:255',
            'house_number' => 'nullable|string|max:50',
            'address_line_2' => 'nullable|string|max:255',
            'city_id' => 'nullable|exists:cities,id',
            'city' => 'required_without:city_id|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_default' => 'boolean',
            'delivery_instructions' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $profile = auth()->user()->profile;
            $cityId = $this->resolveCityId($request);
            if (! $cityId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo resolver la ciudad con los datos enviados',
                ], 422);
            }

            // Si se marca como predeterminada, quitar la marca de las demás
            if ($request->is_default) {
                Address::where('profile_id', $profile->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $address = Address::create([
                'profile_id' => $profile->id,
                'street' => $request->street ?? $request->address_line_1,
                'house_number' => $request->house_number ?? $request->address_line_2,
                'postal_code' => $request->postal_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_default' => $request->is_default ?? false,
                'status' => 'notverified',
                'role' => 'users',
                'city_id' => $cityId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dirección creada exitosamente',
                'data' => [
                    'id' => $address->id,
                    'name' => $request->name ?? $address->street,
                    'address_line_1' => $address->street,
                    'address_line_2' => $address->house_number,
                    'city' => optional($address->city)->name,
                    'state' => optional(optional($address->city)->state)->name,
                    'postal_code' => $address->postal_code,
                    'country' => optional(optional(optional($address->city)->state)->country)->name,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'is_default' => $address->is_default,
                    'delivery_instructions' => null,
                    'formatted_address' => $this->formatAddress($address),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating address: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la dirección',
            ], 500);
        }
    }

    /**
     * Actualizar dirección
     */
    public function updateAddress(Request $request, $addressId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:100',
            'street' => 'required_without:address_line_1|string|max:255',
            'address_line_1' => 'required_without:street|string|max:255',
            'house_number' => 'nullable|string|max:50',
            'address_line_2' => 'nullable|string|max:255',
            'city_id' => 'nullable|exists:cities,id',
            'city' => 'required_without:city_id|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_default' => 'boolean',
            'delivery_instructions' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $profile = auth()->user()->profile;
            $address = Address::where('id', $addressId)
                ->where('profile_id', $profile->id)
                ->firstOrFail();
            $cityId = $this->resolveCityId($request);
            if (! $cityId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo resolver la ciudad con los datos enviados',
                ], 422);
            }

            // Si se marca como predeterminada, quitar la marca de las demás
            if ($request->is_default) {
                Address::where('profile_id', $profile->id)
                    ->where('id', '!=', $addressId)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $address->update([
                'street' => $request->street ?? $request->address_line_1,
                'house_number' => $request->house_number ?? $request->address_line_2,
                'postal_code' => $request->postal_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_default' => $request->is_default ?? $address->is_default,
                'city_id' => $cityId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dirección actualizada exitosamente',
                'data' => [
                    'id' => $address->id,
                    'name' => $request->name ?? $address->street,
                    'address_line_1' => $address->street,
                    'address_line_2' => $address->house_number,
                    'city' => optional($address->city)->name,
                    'state' => optional(optional($address->city)->state)->name,
                    'postal_code' => $address->postal_code,
                    'country' => optional(optional(optional($address->city)->state)->country)->name,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'is_default' => $address->is_default,
                    'delivery_instructions' => null,
                    'formatted_address' => $this->formatAddress($address),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating address: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la dirección',
            ], 500);
        }
    }

    /**
     * Eliminar dirección
     */
    public function deleteAddress($addressId): JsonResponse
    {
        try {
            $profile = auth()->user()->profile;
            $address = Address::where('id', $addressId)
                ->where('profile_id', $profile->id)
                ->firstOrFail();

            // No permitir eliminar la dirección predeterminada si es la única
            if ($address->is_default) {
                $totalAddresses = Address::where('profile_id', $profile->id)->count();
                if ($totalAddresses === 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No puedes eliminar la única dirección disponible',
                    ], 400);
                }
            }

            $address->delete();

            // Si se eliminó la dirección predeterminada, marcar otra como predeterminada
            if ($address->is_default) {
                $newDefault = Address::where('profile_id', $profile->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Dirección eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting address: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la dirección',
            ], 500);
        }
    }

    /**
     * Establecer dirección como predeterminada
     */
    public function setDefaultAddress($addressId): JsonResponse
    {
        try {
            $profile = auth()->user()->profile;
            $address = Address::where('id', $addressId)
                ->where('profile_id', $profile->id)
                ->firstOrFail();

            // Quitar la marca de predeterminada de todas las direcciones
            Address::where('profile_id', $profile->id)
                ->update(['is_default' => false]);

            // Marcar esta dirección como predeterminada
            $address->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Dirección establecida como predeterminada',
            ]);
        } catch (\Exception $e) {
            Log::error('Error setting default address: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al establecer la dirección predeterminada',
            ], 500);
        }
    }

    /**
     * Obtener dirección predeterminada
     */
    public function getDefaultAddress(): JsonResponse
    {
        try {
            $profile = auth()->user()->profile;

            $address = Address::where('profile_id', $profile->id)
                ->where('is_default', true)
                ->first();

            if (! $address) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay dirección predeterminada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $address->id,
                    'name' => $address->street,
                    'address_line_1' => $address->street,
                    'address_line_2' => $address->house_number,
                    'city' => optional($address->city)->name,
                    'state' => optional(optional($address->city)->state)->name,
                    'postal_code' => $address->postal_code,
                    'country' => optional(optional(optional($address->city)->state)->country)->name,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'is_default' => $address->is_default,
                    'delivery_instructions' => null,
                    'formatted_address' => $this->formatAddress($address),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting default address: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la dirección predeterminada',
            ], 500);
        }
    }

    /**
     * Formatear dirección para mostrar
     */
    private function formatAddress(Address $address): string
    {
        $cityName = $address->relationLoaded('city') && $address->city ? $address->city->name : null;
        $parts = array_filter([
            $address->street,
            $address->house_number,
            $cityName,
            $address->postal_code,
        ]);

        return implode(', ', $parts);
    }

    private function resolveCityId(Request $request): ?int
    {
        if ($request->filled('city_id')) {
            return (int) $request->city_id;
        }

        if (! $request->filled('city')) {
            return null;
        }

        $cityQuery = City::query()->where('name', $request->city);
        if ($request->filled('state')) {
            $cityQuery->whereHas('state', function ($q) use ($request) {
                $q->where('name', $request->state);
                if ($request->filled('country')) {
                    $q->whereHas('country', function ($cq) use ($request) {
                        $cq->where('name', $request->country);
                    });
                }
            });
        }

        return optional($cityQuery->first())->id;
    }
}
