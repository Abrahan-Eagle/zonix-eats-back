<?php

namespace App\Console\Commands;

use App\Models\DeliveryCompany;
use App\Services\DeliveryObservabilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PersistDeliveryObservabilitySnapshotsCommand extends Command
{
    protected $signature = 'zonix:observability-snapshots-delivery';

    protected $description = 'Persiste snapshots historicos de observabilidad delivery';

    public function handle(DeliveryObservabilityService $observabilityService): int
    {
        Cache::put('obs:delivery:heartbeat:snapshots_last_run_at', now()->toISOString(), now()->addDay());
        $observabilityService->persistSnapshot(null);
        DeliveryCompany::query()->pluck('id')->each(function ($companyId) use ($observabilityService) {
            $observabilityService->persistSnapshot((int) $companyId);
        });

        $this->info('Snapshots de observabilidad delivery persistidos.');

        return self::SUCCESS;
    }
}
