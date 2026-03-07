<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CouponUsage;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Profile;

class CouponUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = Coupon::where('is_active', true)->get();
        $orders = Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])->get();
        
        if ($coupons->isEmpty() || $orders->isEmpty()) {
            $this->command->warn('No hay cupones activos u órdenes para crear usos.');
            return;
        }
        
        // Crear algunos usos de cupones
        foreach ($orders->take(10) as $order) {
            $coupon = $coupons->random();
            
            CouponUsage::factory()->create([
                'coupon_id' => $coupon->id,
                'profile_id' => $order->profile_id,
                'order_id' => $order->id,
                'discount_amount' => $coupon->discount_type === 'percentage' 
                    ? ($order->total * $coupon->discount_value / 100)
                    : $coupon->discount_value,
            ]);
        }
        
        $this->command->info('CouponUsageSeeder ejecutado exitosamente.');
    }
}
