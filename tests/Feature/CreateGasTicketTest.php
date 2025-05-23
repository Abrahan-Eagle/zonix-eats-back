<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\GasCylinder;
use App\Models\Station;
use App\Models\GasTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CreateGasTicketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_ticket_successfully()
    {
        // Preparación de datos
        $profile = Profile::factory()->create();
        $gasCylinder = GasCylinder::factory()->create();
        $station = Station::factory()->create();

        $data = [
            'profile_id' => $profile->id,
            'gas_cylinders_id' => $gasCylinder->id,
            'station_id' => $station->id,
            'is_external' => false
        ];

        $response = $this->postJson(route('store-gas-ticket'), $data);

        // Asegúrate de que la respuesta sea un código 201
        $response->assertStatus(201);

        // Verificar que el ticket ha sido creado en la base de datos
        $this->assertDatabaseHas('gas_tickets', [
            'profile_id' => $profile->id,
            'gas_cylinders_id' => $gasCylinder->id,
            'station_id' => $station->id,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_returns_error_if_no_station_is_assigned()
    {
        // Preparación de datos
        $profile = Profile::factory()->create();
        $gasCylinder = GasCylinder::factory()->create();

        $data = [
            'profile_id' => $profile->id,
            'gas_cylinders_id' => $gasCylinder->id,
            'station_id' => null,
            'is_external' => false
        ];

        $response = $this->postJson(route('store-gas-ticket'), $data);

        // Verifica que la respuesta es un error 400 con el mensaje esperado
        $response->assertStatus(400)
            ->assertJson(['message' => 'No station assigned to the user']);
    }

    /** @test */
    public function it_prevents_duplicated_tickets_for_pending_status()
    {
        // Preparación de datos
        $profile = Profile::factory()->create();
        $gasCylinder = GasCylinder::factory()->create();
        $station = Station::factory()->create();

        // Crear un ticket existente en la base de datos
        $ticket = GasTicket::factory()->create([
            'profile_id' => $profile->id,
            'gas_cylinders_id' => $gasCylinder->id,
            'station_id' => $station->id,
            'status' => 'pending'
        ]);

        $data = [
            'profile_id' => $profile->id,
            'gas_cylinders_id' => $gasCylinder->id,
            'station_id' => $station->id,
            'is_external' => false
        ];

        $response = $this->postJson(route('store-gas-ticket'), $data);

        // Verifica que no se haya creado un nuevo ticket
        $response->assertStatus(400)
            ->assertJson(['message' => 'You cannot generate a new ticket while another one is pending, verifying, or waiting.']);
    }

    /** @test */
    public function it_checks_ticket_creation_on_sundays_for_external_users()
    {
        // Preparación de datos
        $profile = Profile::factory()->create();
        $gasCylinder = GasCylinder::factory()->create();
        $station = Station::factory()->create();

        // Simulamos que hoy es domingo
        Carbon::setTestNow(Carbon::parse('2024-12-01')); // Domingo

        $data = [
            'profile_id' => $profile->id,
            'gas_cylinders_id' => $gasCylinder->id,
            'station_id' => $station->id,
            'is_external' => true
        ];

        $response = $this->postJson(route('store-gas-ticket'), $data);

        // Verifica que el ticket se haya creado correctamente
        $response->assertStatus(201);
    }

    /** @test */
    public function it_rejects_external_users_on_non_sundays()
    {
        // Preparación de datos
        $profile = Profile::factory()->create();
        $gasCylinder = GasCylinder::factory()->create();
        $station = Station::factory()->create();

        // Simulamos que hoy no es domingo
        Carbon::setTestNow(Carbon::parse('2024-12-02')); // Lunes

        $data = [
            'profile_id' => $profile->id,
            'gas_cylinders_id' => $gasCylinder->id,
            'station_id' => $station->id,
            'is_external' => true
        ];

        $response = $this->postJson(route('store-gas-ticket'), $data);

        // Verifica que la respuesta sea un error 400 con el mensaje esperado
        $response->assertStatus(400)
            ->assertJson(['message' => 'External appointments are only allowed on Sundays']);
    }
}
