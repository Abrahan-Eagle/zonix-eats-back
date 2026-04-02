<?php

namespace App\Console\Commands;

use App\Services\DisputeObservabilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EmitDisputeSlaAlertsCommand extends Command
{
    protected $signature = 'zonix:observability-alerts-disputes';

    protected $description = 'Emite alertas operativas SLA para disputas';

    public function handle(DisputeObservabilityService $observabilityService): int
    {
        Cache::put('obs:disputes:heartbeat:alerts_last_run_at', now()->toISOString(), now()->addDay());
        $observabilityService->emitSlaAlerts();
        $this->info('Alertas SLA de disputas evaluadas.');

        return self::SUCCESS;
    }
}
