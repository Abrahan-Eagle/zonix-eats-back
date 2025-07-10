<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $orders = $this->orderService->getBuyerOrders();
        return response()->json($orders);
    }

    /**
     * Almacena una nueva orden en el sistema.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'commerce_id' => 'required|exists:commerces,id',
            'delivery_type' => 'required|in:pickup,delivery',
            'address' => 'nullable|string'
        ]);

        $order = $this->orderService->createOrder($validated, Auth::id());
        return response()->json(['message' => 'Orden creada con éxito', 'order' => $order], 201);
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
    public function uploadComprobante(Request $request, $id)
    {
        $request->validate([
            'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB máx
        ]);
        $order = \App\Models\Order::where('buyer_id', \Auth::id())->findOrFail($id);
        if ($order->comprobante_url) {
            // Opcional: eliminar comprobante anterior
            \Storage::disk('public')->delete($order->comprobante_url);
        }
        $file = $request->file('comprobante');
        $path = $file->store('comprobantes', 'public');
        $order->comprobante_url = $path;
        $order->estado = 'comprobante_subido'; // Cambia el estado si aplica
        $order->save();
        return response()->json(['message' => 'Comprobante subido', 'comprobante_url' => $path]);
    }
}
