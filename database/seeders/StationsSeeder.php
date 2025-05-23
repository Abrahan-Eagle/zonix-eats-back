<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = [
            [
                'id' => 1,
                'code' => 'CAR_LLD_001', // Código actualizado
                'name' => 'Carabobo Gas C.A.',
                'location' => '2035, Tocuyito 0241, Carabobo',
                'code_plus' => '3WQ4+HG',
                'latitude' => 10.0888875,
                'longitude' => -68.0962378,
                'contact_number' => '04144102449',
                'responsible_person' => 'Carlos Pérez',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 2,
                'code' => 'VAL_LLD_002', // Código actualizado
                'name' => 'Micro Llenadero Dracula Socorro',
                'location' => 'Valencia 2001, Carabobo',
                'code_plus' => '4WHX+GH5',
                'latitude' => 10.1279335,
                'longitude' => -68.0534832,
                'contact_number' => '04142345678', // Número falso
                'responsible_person' => 'Ana González',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 3,
                'code' => 'VAL_LLD_003', // Código actualizado
                'name' => 'Gasdracula La Florida',
                'location' => 'Av principal, Valencia 2001, Carabobo',
                'code_plus' => '5X36+676',
                'latitude' => 10.1519414,
                'longitude' => -68.0800114,
                'contact_number' => '04145678901', // Número falso
                'responsible_person' => 'Luis Rodríguez',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 4,
                'code' => 'VAL_LLD_004', // Código actualizado
                'name' => 'Micro llenadero de Gas Dracula',
                'location' => 'Valencia 2001, Carabobo',
                'code_plus' => '4XRG+4W9',
                'latitude' => 10.1402765,
                'longitude' => -68.0252215,
                'contact_number' => '04147894523', // Número falso
                'responsible_person' => 'Pedro Martínez',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 5,
                'code' => 'VAL_LLD_005', // Código actualizado
                'name' => 'Llenadero De Gas Monumental',
                'location' => 'Valencia 2001, Carabobo',
                'code_plus' => '42Q6+C3W',
                'latitude' => 10.141998,
                'longitude' => -67.9971005,
                'contact_number' => '04148956372', // Número falso
                'responsible_person' => 'Sofía Ramírez',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 6,
                'code' => 'VAL_PLT_006', // Código actualizado
                'name' => 'PDVSA Gas Comunal',
                'location' => 'Av. Soublette, entre y Cantaura, C.C. Profesional Center, PB., Calle Silva, Valencia, Carabobo',
                'code_plus' => '5XGV+78P',
                'latitude' => 10.1757005,
                'longitude' => -68.0092523,
                'contact_number' => '04149478322', // Número falso
                'responsible_person' => 'María Fernández',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 7,
                'code' => 'VAL_PLT_007', // Código actualizado
                'name' => 'Planta De Llenado Negra Hipolita (Gas Dracula)',
                'location' => '7 Trans. 8, Valencia 2003, Carabobo',
                'code_plus' => '52CG+G7',
                'latitude' => 10.1713024,
                'longitude' => -67.9740619,
                'contact_number' => '04141567890', // Número falso
                'responsible_person' => 'Javier López',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 8,
                'code' => 'NAG_LLD_008', // Código actualizado
                'name' => 'Llenadero de Guaparo',
                'location' => 'Av. La Hispanidad, Naguanagua 2005, Carabobo',
                'code_plus' => '6XHR+CWQ',
                'latitude' => 10.228595,
                'longitude' => -68.0102259,
                'contact_number' => '04143128934', // Número falso
                'responsible_person' => 'Gabriela Torres',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 9,
                'code' => 'GUA_LLD_009', // Código actualizado
                'name' => 'PDVSA GAS',
                'location' => 'Guacara 2015, Carabobo',
                'code_plus' => '638W+436',
                'latitude' => 10.2285846,
                'longitude' => -68.090054,
                'contact_number' => '04149872561', // Número falso
                'responsible_person' => 'Fernando Gómez',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 10,
                'code' => 'GUA_PLT_010', // Código actualizado
                'name' => 'AUTOGAS LLENADERO PDVSA',
                'location' => 'Variante Yagua, Guacara 2015, Carabobo',
                'code_plus' => '63RP+G54',
                'latitude' => 10.2412654,
                'longitude' => -67.9866385,
                'contact_number' => '04146782314', // Número falso
                'responsible_person' => 'Elena Pérez',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 11,
                'code' => 'GUA_PLT_011', // Código actualizado
                'name' => 'Planta Yagua PDVSA',
                'location' => 'Guacara 2015, Carabobo',
                'code_plus' => '732M+5F',
                'latitude' => 10.2504995,
                'longitude' => -67.9188444,
                'contact_number' => '04148963547', // Número falso
                'responsible_person' => 'Luis Torres',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
            [
                'id' => 12,
                'code' => 'GUA_LLD_012', // Código actualizado
                'name' => 'Centro de llenado de gas',
                'location' => 'Guacara 2015, Carabobo',
                'code_plus' => '64W6+7QW',
                'latitude' => 10.2504891,
                'longitude' => -67.9986725,
                'contact_number' => '04149765432', // Número falso
                'responsible_person' => 'Carlos Herrera',
                'days_available' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'active' => true,
            ],
        ];

        // DB::table('stations')->insert($stations);

        foreach ($stations as $station) {
            $stationx = Station::create($station);
            echo $stationx . " STATIONS";
        }
    }
}
