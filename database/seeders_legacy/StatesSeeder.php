<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Estados de Venezuela (24 entidades federales)
     *
     * @return void
     */
    public function run()
    {
        $states = [
            ['id' => '1', 'name' => 'Amazonas', 'countries_id' => '1'],
            ['id' => '2', 'name' => 'Anzoátegui', 'countries_id' => '1'],
            ['id' => '3', 'name' => 'Apure', 'countries_id' => '1'],
            ['id' => '4', 'name' => 'Aragua', 'countries_id' => '1'],
            ['id' => '5', 'name' => 'Barinas', 'countries_id' => '1'],
            ['id' => '6', 'name' => 'Bolívar', 'countries_id' => '1'],
            ['id' => '7', 'name' => 'Carabobo', 'countries_id' => '1'],
            ['id' => '8', 'name' => 'Cojedes', 'countries_id' => '1'],
            ['id' => '9', 'name' => 'Delta Amacuro', 'countries_id' => '1'],
            ['id' => '10', 'name' => 'Distrito Capital', 'countries_id' => '1'],
            ['id' => '11', 'name' => 'Falcón', 'countries_id' => '1'],
            ['id' => '12', 'name' => 'Guárico', 'countries_id' => '1'],
            ['id' => '13', 'name' => 'Lara', 'countries_id' => '1'],
            ['id' => '14', 'name' => 'Mérida', 'countries_id' => '1'],
            ['id' => '15', 'name' => 'Miranda', 'countries_id' => '1'],
            ['id' => '16', 'name' => 'Monagas', 'countries_id' => '1'],
            ['id' => '17', 'name' => 'Nueva Esparta', 'countries_id' => '1'],
            ['id' => '18', 'name' => 'Portuguesa', 'countries_id' => '1'],
            ['id' => '19', 'name' => 'Sucre', 'countries_id' => '1'],
            ['id' => '20', 'name' => 'Táchira', 'countries_id' => '1'],
            ['id' => '21', 'name' => 'Trujillo', 'countries_id' => '1'],
            ['id' => '22', 'name' => 'La Guaira', 'countries_id' => '1'],
            ['id' => '23', 'name' => 'Yaracuy', 'countries_id' => '1'],
            ['id' => '24', 'name' => 'Zulia', 'countries_id' => '1'],
        ];

        foreach ($states as $state) {
            State::updateOrCreate(
                ['id' => $state['id']],
                $state
            );
        }
    }
}
