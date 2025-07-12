<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\OrderCreated;

/**
 * Controlador para gestionar las órdenes del comprador.
 *
 * Métodos principales:
 * - index(): Listar órdenes del comprador autenticado.
 * - store(): Crear una nueva orden.
 */
class OrderController extends Controller
{
    /**
     * Servicio de órdenes.
     * @var OrderService
     */
    protected $orderService;

    /**
     * Inyecta el servicio de órdenes.
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Listar las órdenes del comprador autenticado.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $orders = $this->orderService->getUserOrders();
        return response()->json($orders);
    }

    /**
     * Almacena una nueva orden en el sistema.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'commerce_id' => 'required|exists:commerces,id',
                'products' => 'required|array|min:1',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'delivery_type' => 'required|in:pickup,delivery',
                'total' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:500',
                'delivery_address' => 'nullable|string|max:500',
            ]);

            $user = Auth::user();
            $profile = $user->profile;
            
            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario sin perfil creado'
                ], 400);
            }

            // Crear la orden
            $order = \App\Models\Order::create([
                'profile_id' => $profile->id,
                'commerce_id' => $validated['commerce_id'],
                'delivery_type' => $validated['delivery_type'],
                'status' => 'pending_payment',
                'total' => $validated['total'],
                'notes' => $validated['notes'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
            ]);

            // Agregar productos a la orden
            foreach ($validated['products'] as $product) {
                $productModel = \App\Models\Product::find($product['id']);
                $order->products()->attach($product['id'], [
                    'quantity' => $product['quantity'],
                    'unit_price' => $productModel->price
                ]);
            }

            // Emitir evento de nueva orden
            // event(new OrderCreated($order));

            return response()->json([
                'success' => true,
                'message' => 'Orden creada exitosamente',
                'data' => $order->load('products')
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra los detalles de una orden específica.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $order = $this->orderService->getOrderDetails($id, Auth::id());
        if (!$order) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }
        return response()->json($order);
    }

    /**
     * Cancela una orden pendiente.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        $result = $this->orderService->cancelOrder($id, Auth::id());
        if ($result === true) {
            return response()->json(['message' => 'Orden cancelada']);
        }
        return response()->json(['error' => $result], 400);
    }

    /**
     * Subir comprobante de pago para una orden.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadPaymentProof(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'payment_method' => 'required|string|max:100',
            'reference_number' => 'required|string|max:100',
        ]);

        $user = Auth::user();
        $profile = $user->profile;
        
        $order = \App\Models\Order::where('profile_id', $profile->id)->findOrFail($id);
        
        if ($order->status === 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede subir comprobante para una orden completada'
            ], 400);
        }

        $file = $request->file('payment_proof');
        $path = $file->store('payment_proofs', 'public');
        
        $order->update([
            'payment_proof' => 'payment_proofs/' . $file->hashName(),
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'status' => 'pending_payment'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comprobante de pago subido exitosamente'
        ]);
    }

    /**
     * Cancelar una orden.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $profile = $user->profile;
        
        $order = \App\Models\Order::where('profile_id', $profile->id)->findOrFail($id);
        
        if ($order->status === 'preparing') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar una orden en preparación'
            ], 400);
        }

        $order->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Orden cancelada exitosamente'
        ]);
    }
}
