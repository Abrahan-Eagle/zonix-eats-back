<?php

namespace Database\Factories;

use App\Models\OpticalPartner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OpticalPartner>
 */
class OpticalPartnerFactory extends Factory
{
    protected $model = OpticalPartner::class;

    public function definition(): array
    {
        $name = $this->faker->company().' Óptica';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numerify('###'),
            'tax_id' => null,
            'is_zonix_direct' => false,
            'commission_rate' => 0.10,
            'status' => 'active',
        ];
    }

    public function zonixDirect(): Factory
    {
        return $this->state([
            'name' => 'Zonix Direct',
            'slug' => 'zonix-direct',
            'is_zonix_direct' => true,
            'commission_rate' => 0,
        ]);
    }
}
