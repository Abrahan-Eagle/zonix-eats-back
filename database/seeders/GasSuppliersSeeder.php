<?php

namespace Database\Seeders;

use App\Models\GasSupplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GasSuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $GasSuppliersSeeders = [
            ['id' => 1, 'name' => 'Pdvsa Gas', 'contact_info' => null, 'address' => null],
            ['id' => 2, 'name' => 'Gavenplast', 'contact_info' => null, 'address' => null],
            ['id' => 3, 'name' => 'Hexagon Ragasco', 'contact_info' => null, 'address' => null],
            ['id' => 4, 'name' => 'Industrias VenGas', 'contact_info' => null, 'address' => null],
            ['id' => 5, 'name' => 'Gas Comunal', 'contact_info' => null, 'address' => null],
            ['id' => 6, 'name' => 'Cilindros de Gas del Centro', 'contact_info' => null, 'address' => null],
            ['id' => 7, 'name' => 'Gas Licuado de Venezuela (GLV)', 'contact_info' => null, 'address' => null],
            ['id' => 8, 'name' => 'Gas Guárico', 'contact_info' => null, 'address' => null],
            ['id' => 9, 'name' => 'Gas Lara', 'contact_info' => null, 'address' => null],
            ['id' => 10, 'name' => 'Gas Anzoátegui', 'contact_info' => null, 'address' => null]
        ];

        // Insertar datos en la tabla country Code
        foreach ($GasSuppliersSeeders as $GasSupplierSeeder) {
            $varGasSuppliersSeeder = GasSupplier::create($GasSupplierSeeder);
            echo $varGasSuppliersSeeder . "GasSupplier:";
        }

    }
}
