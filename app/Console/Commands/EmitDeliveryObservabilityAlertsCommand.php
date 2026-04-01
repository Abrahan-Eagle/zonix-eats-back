<?php

namespace App\Console\Commands;

use App\Services\DeliveryObservabilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EmitDeliveryObservabilityAlertsCommand extends Command
{
    protected $signature = 'zonix:observability-alerts-delivery';

    protected $description = 'Emite alertas operativas de observabilidad de delivery';

    public function handle(DeliveryObservabilityService $observabilityService): int
    {
        Cache::put('obs:delivery:heartbeat:alerts_last_run_at', now()->toISOString(), now()->addDay());
        $observabilityService->emitOperationalAlerts();
        $this->info('Alertas de observabilidad delivery emitidas.');

        return self::SUCCESS;
    }
}
