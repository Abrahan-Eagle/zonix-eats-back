# Code Style — Zonix Eats Backend

## Naming Conventions

| Tipo       | Convención       | Ejemplo              |
| ---------- | ---------------- | -------------------- |
| Archivos   | snake_case       | `order_service.php`  |
| Clases     | PascalCase       | `OrderService`       |
| Métodos    | camelCase        | `getUserOrders()`    |
| Variables  | camelCase        | `$orderId`           |
| Constantes | UPPER_SNAKE_CASE | `MAX_RETRY_ATTEMPTS` |

## Controller Pattern

```php
<?php
namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = $this->orderService->getUserOrders();
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
}
```

## Service Pattern

```php
<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public function getUserOrders()
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return collect();
        }

        return Order::where('profile_id', $profile->id)
            ->with(['commerce', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

## Key Rules

1. **Lógica de negocio en Services, NUNCA en Controllers**
2. **SIEMPRE usar Form Requests para validación**
3. **SIEMPRE usar `with()` para eager loading** (evitar N+1)
4. **SIEMPRE usar `DB::transaction()` para operaciones críticas**
5. **SIEMPRE usar `config()` en vez de `env()` en controllers** (compatible con `config:cache`)
6. **Uploads: SIEMPRE validar `max:5120`** (5MB máximo)
