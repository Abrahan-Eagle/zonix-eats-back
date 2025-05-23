<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GasCylinder;
use App\Models\Profile;
use App\Models\GasSupplier;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GasCylinder>
 */
class GasCylinderFactory extends Factory
{

    protected $model = GasCylinder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $supplier = GasSupplier::inRandomOrder()->first();
        // $profile = Profile::inRandomOrder()->first();
        $profile = Profile::orderBy('id')->first();



        return [
            'gas_cylinder_code' => $this->faker->unique()->bothify('CYL-###'),
            'cylinder_quantity' => $this->faker->numberBetween(1, 5),
            'cylinder_type' => $this->faker->randomElement(['small', 'wide']),
            'cylinder_weight' => $this->faker->randomElement(['10kg', '18kg', '45kg']),
            'approved' => $this->faker->boolean(),
            'photo_gas_cylinder' => null, // puedes agregar una imagen si lo necesitas
            'manufacturing_date' => $this->faker->date('Y-m-d'),
            // 'profile_id' => Profile::factory(), // Relación con Profile
            'profile_id' => $profile ? $profile->id : null, // Relación con Profile
            // 'company_supplier_id' => GasSupplier::factory(), // Relación con GasSupplier, si lo necesitas
            'company_supplier_id' => $supplier ? $supplier->id : null, // Maneja si no hay proveedores
        ];
    }
}
