<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\Profile;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_update_or_delete_foreign_address(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $otherProfile = Profile::factory()->create(['user_id' => $otherUser->id]);

        $country = Country::create(['sortname' => 'VE', 'name' => 'Venezuela', 'phonecode' => 58]);
        $state = State::create(['name' => 'Carabobo', 'countries_id' => $country->id]);
        $city = City::create(['name' => 'Valencia', 'state_id' => $state->id]);

        $address = Address::create([
            'street' => 'Calle 1',
            'house_number' => '12',
            'postal_code' => '2001',
            'latitude' => 10.20,
            'longitude' => -68.00,
            'status' => 'notverified',
            'profile_id' => $otherProfile->id,
            'city_id' => $city->id,
            'role' => 'user',
        ]);

        $show = $this->actingAs($user, 'sanctum')->getJson("/api/addresses/{$address->id}");
        $show->assertStatus(403);

        $update = $this->actingAs($user, 'sanctum')->putJson("/api/addresses/{$address->id}", [
            'street' => 'Calle 2',
        ]);
        $update->assertStatus(403);

        $delete = $this->actingAs($user, 'sanctum')->deleteJson("/api/addresses/{$address->id}");
        $delete->assertStatus(403);
    }

    public function test_user_can_create_own_address(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $country = Country::create(['sortname' => 'VE', 'name' => 'Venezuela', 'phonecode' => 58]);
        $state = State::create(['name' => 'Carabobo', 'countries_id' => $country->id]);
        $city = City::create(['name' => 'Valencia', 'state_id' => $state->id]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/addresses', [
            'profile_id' => $profile->id,
            'street' => 'Av Principal',
            'house_number' => '10',
            'postal_code' => '2001',
            'latitude' => 10.20,
            'longitude' => -68.00,
            'city_id' => $city->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('addresses', [
            'profile_id' => $profile->id,
            'street' => 'Av Principal',
        ]);
    }
}
