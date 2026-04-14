<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\City;
use App\Models\Commerce;
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
        $user = User::factory()->create(['role' => 'users']);
        $otherUser = User::factory()->create(['role' => 'users']);
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
            'role' => 'users',
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

    public function test_user_can_manage_own_commerce_address_with_null_profile_id(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);

        $country = Country::create(['sortname' => 'VE', 'name' => 'Venezuela', 'phonecode' => 58]);
        $state = State::create(['name' => 'Carabobo', 'countries_id' => $country->id]);
        $city = City::create(['name' => 'Valencia', 'state_id' => $state->id]);

        $address = Address::create([
            'street' => 'Av Comercio',
            'house_number' => '10',
            'postal_code' => '2001',
            'latitude' => 10.20,
            'longitude' => -68.00,
            'status' => 'notverified',
            'profile_id' => null,
            'city_id' => $city->id,
            'role' => 'commerce',
            'commerce_id' => $commerce->id,
        ]);

        $show = $this->actingAs($user, 'sanctum')->getJson("/api/addresses/{$address->id}");
        $show->assertStatus(200);

        $update = $this->actingAs($user, 'sanctum')->putJson("/api/addresses/{$address->id}", [
            'street' => 'Av Comercio 2',
        ]);
        $update->assertStatus(200);

        $delete = $this->actingAs($user, 'sanctum')->deleteJson("/api/addresses/{$address->id}");
        $delete->assertStatus(200);
    }

    public function test_non_admin_cannot_reassign_profile_id_on_update(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        $otherUser = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
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
            'profile_id' => $profile->id,
            'city_id' => $city->id,
            'role' => 'users',
        ]);

        $this->actingAs($user, 'sanctum')->putJson("/api/addresses/{$address->id}", [
            'profile_id' => $otherProfile->id,
            'street' => 'Calle 2',
        ])->assertStatus(200);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'profile_id' => $profile->id,
            'street' => 'Calle 2',
        ]);
    }
}
