<?php

namespace Database\Seeders;

use App\Models\OperatorCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperatorCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $operatorCodes = [
            ['id' => 1,'name' => '0412', 'code' => 0412],
            ['id' => 2,'name' => '0414', 'code' => 0414],
            ['id' => 3,'name' => '0424', 'code' => 0424],
            ['id' => 4,'name' => '0416', 'code' => 0416],
            ['id' => 5,'name' => '0426', 'code' => 0426],
          ];

        // Insertar o actualizar (permite re-ejecutar el seeder sin duplicados)
        foreach ($operatorCodes as $operatorCode) {
            OperatorCode::updateOrCreate(
                ['id' => $operatorCode['id']],
                ['name' => $operatorCode['name'], 'code' => $operatorCode['code']]
            );
        }
        $this->command->info('OperatorCodeSeeder ejecutado.');

    }
}
