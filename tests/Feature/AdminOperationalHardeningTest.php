<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminOperationalHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_settings_roundtrip_uses_updated_values(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $payload = [
            'app_name' => 'Zonix Eats Ops',
            'maintenance_mode' => true,
            'registration_enabled' => false,
            'email_verification_required' => true,
            'phone_verification_required' => true,
        ];

        $this->putJson('/api/admin/settings', $payload)
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->getJson('/api/admin/settings')
            ->assertStatus(200)
            ->assertJsonPath('app_name', 'Zonix Eats Ops')
            ->assertJsonPath('maintenance_mode', true)
            ->assertJsonPath('registration_enabled', false)
            ->assertJsonPath('email_verification_required', true)
            ->assertJsonPath('phone_verification_required', true);
    }

    public function test_admin_security_logs_returns_recent_admin_actions(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->putJson('/api/admin/settings', [
            'app_name' => 'Zonix Security',
        ])->assertStatus(200);

        $response = $this->getJson('/api/admin/security-logs?per_page=5');
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $logs = $response->json('data');
        $this->assertIsArray($logs);
        $this->assertNotEmpty($logs);
        $this->assertSame('api/admin/settings', $logs[0]['path'] ?? null);
        $this->assertSame('PUT', $logs[0]['method'] ?? null);
    }

    public function test_admin_security_logs_supports_status_filter_and_pagination(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->putJson('/api/admin/settings', [
            'app_name' => 'Zonix Filters',
        ])->assertStatus(200);

        // Fuerza una acción fallida (422) para validar filtro de error.
        $this->putJson('/api/admin/users/'.$admin->id.'/status', [
            'status' => 'invalid_status',
        ])->assertStatus(422);

        $successResponse = $this->getJson('/api/admin/security-logs?status=success&per_page=1');
        $successResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('pagination.per_page', 1);

        $successLogs = $successResponse->json('data');
        $this->assertNotEmpty($successLogs);
        $this->assertTrue((bool) ($successLogs[0]['success'] ?? false));

        $errorResponse = $this->getJson('/api/admin/security-logs?status=error');
        $errorResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $errorLogs = $errorResponse->json('data');
        $this->assertNotEmpty($errorLogs);
        $this->assertFalse((bool) ($errorLogs[0]['success'] ?? true));
    }
}
