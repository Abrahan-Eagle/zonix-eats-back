<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CommerceInvoice;
use App\Models\Commerce;
use App\Models\Order;
use Carbon\Carbon;

class CommerceInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commerces = Commerce::all();
        
        if ($commerces->isEmpty()) {
            $this->command->warn('No hay comercios para crear facturas.');
            return;
        }
        
        foreach ($commerces as $commerce) {
            // Crear factura mensual para cada comercio
            $membershipFee = $commerce->membership_monthly_fee ?? 50.00;
            
            // Calcular comisiones del mes (suma de commission_amount de órdenes del mes)
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            
            $commissionAmount = Order::where('commerce_id', $commerce->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
                ->sum('commission_amount');
            
            $total = $membershipFee + $commissionAmount;
            
            if ($total > 0) {
                CommerceInvoice::factory()->create([
                    'commerce_id' => $commerce->id,
                    'membership_fee' => $membershipFee,
                    'commission_amount' => $commissionAmount,
                    'total' => $total,
                    'invoice_date' => Carbon::now(),
                    'due_date' => Carbon::now()->addMonth(),
                    'status' => collect(['pending', 'paid', 'overdue'])->random(),
                ]);
            }
        }
        
        $this->command->info('CommerceInvoiceSeeder ejecutado exitosamente.');
    }
}
