<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Commerce;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Búsqueda avanzada de restaurantes
     */
    public function searchRestaurants(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'search' => 'nullable|string|max:120',
                'open' => 'nullable|boolean',
                'business_type_id' => 'nullable|integer|exists:business_types,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'min_rating' => 'nullable|numeric|min:0|max:5',
                'sort_by' => 'nullable|in:name,rating,delivery_time',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $query = Commerce::query()
                ->select([
                    'id',
                    'business_name',
                    'business_type',
                    'business_type_id',
                    'address',
                    'phone',
                    'image',
                    'open',
                    'delivery_fee',
                    'minimum_order',
                    'preparation_time',
                    'status',
                ])
                ->with([
                    'products' => function ($productQuery) {
                        $productQuery->where('available', true)->select(['id', 'commerce_id', 'name', 'price', 'image']);
                    },
                    'businessTypeRelation',
                    'addresses',
                    'phones',
                ])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('status', 'approved');

            // Búsqueda por nombre
            if ($request->filled('search')) {
                $searchTerm = trim((string) $request->search);
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('business_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('business_type', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas('products', function ($productQuery) use ($searchTerm) {
                            $productQuery->where('name', 'LIKE', "%{$searchTerm}%");
                        });
                });
            }

            // Filtro por estado abierto/cerrado
            if ($request->filled('open')) {
                $query->where('open', filter_var($request->open, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false);
            } else {
                $query->where('open', true);
            }

            // Filtro por tipo de negocio
            if ($request->filled('business_type_id')) {
                $query->where('business_type_id', (int) $request->business_type_id);
            }

            // Filtro por categoría de producto
            if ($request->filled('category_id')) {
                $categoryId = (int) $request->category_id;
                $query->whereHas('products', function ($productQuery) use ($categoryId) {
                    $productQuery->where('category_id', $categoryId);
                });
            }

            // Filtro por precio mínimo
            if ($request->filled('min_price')) {
                $query->whereHas('products', function ($productQuery) use ($request) {
                    $productQuery->where('price', '>=', $request->min_price);
                });
            }

            // Filtro por precio máximo
            if ($request->filled('max_price')) {
                $query->whereHas('products', function ($productQuery) use ($request) {
                    $productQuery->where('price', '<=', $request->max_price);
                });
            }
            if ($request->filled('min_price') && $request->filled('max_price')
                && (float) $request->min_price > (float) $request->max_price) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Parámetros de búsqueda inválidos',
                    'errors' => [
                        'min_price' => ['min_price no puede ser mayor que max_price'],
                    ],
                ], 422);
            }

            // Filtro por calificación mínima (promedio de reviews)
            if ($request->filled('min_rating')) {
                $query->having('reviews_avg_rating', '>=', (float) $request->min_rating);
            }

            $allowedSortBy = ['name', 'rating', 'delivery_time'];
            $sortBy = in_array($request->sort_by, $allowedSortBy, true) ? $request->sort_by : 'name';
            $sortOrder = strtolower((string) $request->sort_order) === 'desc' ? 'desc' : 'asc';

            if ($sortBy === 'rating') {
                $query->orderBy('reviews_avg_rating', $sortOrder);
            } elseif ($sortBy === 'delivery_time') {
                $query->orderBy('preparation_time', $sortOrder);
            } else {
                $query->orderBy('business_name', $sortOrder);
            }

            // Paginación
            $perPage = min(max((int) ($request->per_page ?? 20), 1), 100);
            $restaurants = $query->paginate($perPage);

            $favoriteIds = [];
            $profile = $request->user()?->profile;
            if ($profile && method_exists($profile, 'favorites')) {
                $favoriteIds = $profile->favorites()->pluck('commerce_id')->all();
            }

            $restaurantsData = $restaurants->getCollection()->map(function ($restaurant) use ($favoriteIds) {
                $data = [
                    'id' => $restaurant->id,
                    'name' => $restaurant->business_name,
                    'description' => $restaurant->business_type,
                    'category' => $restaurant->businessTypeRelation?->name ?? $restaurant->business_type,
                    'address' => $restaurant->address,
                    'phone' => $restaurant->phone,
                    'logo_url' => $restaurant->image,
                    'cover_url' => $restaurant->image,
                    'average_rating' => $restaurant->reviews_avg_rating ? round((float) $restaurant->reviews_avg_rating, 1) : 0,
                    'total_reviews' => (int) ($restaurant->reviews_count ?? 0),
                    'estimated_delivery_time' => $restaurant->preparation_time ?? 30,
                    'delivery_fee' => $restaurant->delivery_fee ?? 0,
                    'minimum_order' => $restaurant->minimum_order ?? 0,
                    'is_open' => (bool) $restaurant->open,
                    'is_favorite' => in_array($restaurant->id, $favoriteIds, true),
                    'total_products' => $restaurant->products->count(),
                ];

                // Agregar productos destacados
                $data['featured_products'] = $restaurant->products
                    ->take(3)
                    ->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => (float) $product->price,
                            'image_url' => $product->image,
                        ];
                    });

                return $data;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $restaurantsData,
                    'restaurants' => $restaurantsData,
                    // Legacy compatibility
                    'data' => $restaurantsData,
                    'pagination' => [
                        'current_page' => $restaurants->currentPage(),
                        'last_page' => $restaurants->lastPage(),
                        'per_page' => $restaurants->perPage(),
                        'total' => $restaurants->total(),
                    ],
                    'filters_applied' => [
                        'search' => $request->search,
                        'business_type_id' => $request->business_type_id,
                        'category_id' => $request->category_id,
                        'open' => $request->open,
                        'min_price' => $request->min_price,
                        'max_price' => $request->max_price,
                        'min_rating' => $request->min_rating,
                        'sort_by' => $request->sort_by,
                        'sort_order' => $request->sort_order,
                    ],
                ],
                'message' => 'Restaurantes encontrados exitosamente',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Parámetros de búsqueda inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error searching restaurants: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error al buscar restaurantes',
            ], 500);
        }
    }

    /**
     * Búsqueda de productos
     */
    public function searchProducts(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'search' => 'nullable|string|max:120',
                'commerce_id' => 'nullable|integer|exists:commerces,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'available' => 'nullable|boolean',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'sort_by' => 'nullable|in:name,price,created_at',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $query = Product::with(['commerce', 'category'])
                ->whereHas('commerce', function ($commerceQuery) {
                    $commerceQuery->where('status', 'approved')
                        ->where('open', true);
                });

            // Búsqueda por nombre o descripción
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filtro por restaurante
            if ($request->filled('commerce_id')) {
                $query->where('commerce_id', $request->commerce_id);
            }

            // Filtro por categoría
            if ($request->filled('category_id')) {
                $query->where('category_id', (int) $request->category_id);
            }

            // Filtro por precio
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }
            if ($request->filled('min_price') && $request->filled('max_price')
                && (float) $request->min_price > (float) $request->max_price) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Parámetros de búsqueda inválidos',
                    'errors' => [
                        'min_price' => ['min_price no puede ser mayor que max_price'],
                    ],
                ], 422);
            }

            // Filtro por disponibilidad
            if ($request->filled('available')) {
                $query->where('available', filter_var($request->available, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false);
            } else {
                $query->where('available', true);
            }

            // Ordenamiento
            $allowedSortBy = ['name', 'price', 'created_at'];
            $sortBy = in_array($request->sort_by, $allowedSortBy, true) ? $request->sort_by : 'name';
            $sortOrder = strtolower((string) $request->sort_order) === 'desc' ? 'desc' : 'asc';

            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = min(max((int) ($request->per_page ?? 20), 1), 100);
            $products = $query->paginate($perPage);

            $productsData = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'commerce_id' => $product->commerce_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category?->name,
                    'category' => $product->category?->name,
                    'image' => $product->image,
                    'image_url' => $product->image,
                    'available' => (bool) $product->available,
                    'is_available' => (bool) $product->available,
                    'stock_quantity' => $product->stock_quantity,
                    'commerce' => [
                        'id' => $product->commerce->id,
                        'name' => $product->commerce->business_name,
                        'logo_url' => $product->commerce->image,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $productsData,
                    'products' => $productsData,
                    // Legacy compatibility
                    'data' => $productsData,
                    'pagination' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                    ],
                ],
                'message' => 'Productos encontrados exitosamente',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Parámetros de búsqueda inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error searching products: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error al buscar productos',
            ], 500);
        }
    }

    /**
     * Obtener categorías disponibles
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = Category::select('id', 'name', 'description')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categorías obtenidas exitosamente',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting categories: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener las categorías',
            ], 500);
        }
    }

    /**
     * Obtener sugerencias de búsqueda
     */
    public function getSearchSuggestions(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->search ?? '';

            if (strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Sugerencias obtenidas exitosamente',
                ]);
            }

            // Sugerencias de restaurantes
            $restaurantSuggestions = Commerce::where('status', 'approved')
                ->where('business_name', 'LIKE', "%{$searchTerm}%")
                ->limit(5)
                ->pluck('business_name');

            // Sugerencias de productos
            $productSuggestions = Product::where('available', true)
                ->whereHas('commerce', function ($query) {
                    $query->where('status', 'approved')->where('open', true);
                })
                ->where('name', 'LIKE', "%{$searchTerm}%")
                ->limit(5)
                ->pluck('name');

            $suggestions = $restaurantSuggestions->merge($productSuggestions)
                ->unique()
                ->values()
                ->take(10);

            return response()->json([
                'success' => true,
                'data' => $suggestions,
                'message' => 'Sugerencias obtenidas exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting search suggestions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener las sugerencias',
            ], 500);
        }
    }

    /**
     * Verificar si un restaurante está en favoritos
     */
    private function isFavorite($commerceId): bool
    {
        $profile = auth()->user()->profile;

        return $profile->favorites()
            ->where('commerce_id', $commerceId)
            ->exists();
    }
}
