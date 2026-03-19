# Testing — Zonix Eats Backend

## Comandos

```bash
php artisan test                       # Todos (269 tests)
php artisan test --filter=OrderTest   # Específico
php artisan test --coverage            # Coverage
```

## Test Pattern

```php
<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order()
    {
        $user = User::factory()->create(['role' => 'users']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/buyer/orders', [...]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);
    }
}
```
