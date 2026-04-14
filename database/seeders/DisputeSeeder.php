<?php

namespace Database\Seeders;

use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class DisputeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::whereIn('status', ['delivered', 'cancelled'])->get();

        if ($orders->isEmpty()) {
            $this->command->warn('No hay órdenes para crear disputas.');

            return;
        }

        // Crear algunas disputas
        foreach ($orders->take(5) as $order) {
            $reportedBy = $order->profile;

            // Disputa contra comercio
            if (rand(0, 1)) {
                Dispute::factory()->create([
                    'order_id' => $order->id,
                    'reported_by_type' => Profile::class,
                    'reported_by_id' => $reportedBy->id,
                    'reported_against_type' => Commerce::class,
                    'reported_against_id' => $order->commerce_id,
                ]);
            }

            // Disputa contra delivery (si tiene)
            if ($order->delivery_type === 'delivery' && $order->orderDelivery) {
                if (rand(0, 1)) {
                    Dispute::factory()->create([
                        'order_id' => $order->id,
                        'reported_by_type' => Profile::class,
                        'reported_by_id' => $reportedBy->id,
                        'reported_against_type' => DeliveryAgent::class,
                        'reported_against_id' => $order->orderDelivery->agent_id,
                    ]);
                }
            }
        }

        $this->command->info('DisputeSeeder ejecutado exitosamente.');
    }
}
