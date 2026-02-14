<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    /**
     * Seed the application's database.
     * 
     * Este seeder organiza el orden de ejecución de todos los seeders.
     * Cada seeder específico maneja su propia lógica de creación de datos usando factories.
     */
    public function run(): void
    {
        $this->call([
            // Seeders de datos base (deben ejecutarse primero)
            RoleSeeder::class,
            BanksSeeder::class,
            OperatorCodeSeeder::class,
            CountriesSeeder::class,
            StatesSeeder::class,
            CitiesSeeder::class,
            CategorySeeder::class,
            
            // Seeders de usuarios y perfiles
            UserSeeder::class,
            
            // Seeders de comercios (AddressSeeder después para nearby-places)
            CommerceSeeder::class,
            AddressSeeder::class,
            ProductSeeder::class,
            ProductExtraSeeder::class,
            ProductPreferenceSeeder::class,
            
            // Seeders de delivery
            DeliveryCompanySeeder::class,
            DeliveryAgentSeeder::class,
            
            // Seeders de órdenes
            OrderSeeder::class,
            OrderItemSeeder::class,
            OrderDeliverySeeder::class,
            
            // Seeders de carrito
            CartSeeder::class,
            CartItemSeeder::class,
            
            // Seeders de ubicaciones (AddressSeeder ya ejecutado tras CommerceSeeder)
            UserLocationSeeder::class,
            
            // Seeders de promociones y cupones
            PromotionSeeder::class,
            CouponSeeder::class,
            CouponUsageSeeder::class,
            
            // Seeders de reviews y disputas
            ReviewSeeder::class,
            DisputeSeeder::class,
            
            // Seeders de pagos y facturas
            PaymentMethodSeeder::class,
            DeliveryPaymentSeeder::class,
            CommerceInvoiceSeeder::class,
            
            // Seeders de posts y notificaciones
            PostSeeder::class,
            PostLikeSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
