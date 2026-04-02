<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\Profile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DisputeObservabilityService
{
    public function emitSlaAlerts(): void
    {
        $pending6h = $this->pendingOlderThanHours(6);
        $pending12h = $this->pendingOlderThanHours(12);
        $pending24h = $this->pendingOlderThanHours(24);
        $pending72h = $this->pendingOlderThanHours(72);

        $alertLevel = $this->resolveAlertLevel($pending12h, $pending24h, $pending72h);
        if ($alertLevel === null) {
            return;
        }

        $dedupeMinutes = (int) config('zonix.observability.alert_dedupe_minutes', 30);
        $cacheKey = sprintf(
            'obs:disputes:sla:%s:%d:%d:%d',
            $alertLevel,
            $pending12h,
            $pending24h,
            $pending72h
        );
        if (Cache::has($cacheKey)) {
            return;
        }

        $payload = [
            'module' => 'disputes_observability',
            'alert_level' => $alertLevel,
            'pending_older_than_6h' => $pending6h,
            'pending_older_than_12h' => $pending12h,
            'pending_older_than_24h' => $pending24h,
            'pending_older_than_72h' => $pending72h,
        ];

        Log::warning('disputes_sla_alert', $payload);
        $this->notifyAdmins($payload);

        Cache::put($cacheKey, true, now()->addMinutes($dedupeMinutes));
    }

    private function notifyAdmins(array $payload): void
    {
        $adminProfiles = Profile::whereHas('user', fn ($q) => $q->where('role', 'admin'))->pluck('id');
        if ($adminProfiles->isEmpty()) {
            return;
        }

        $notificationService = app(NotificationService::class);
        foreach ($adminProfiles as $profileId) {
            $notificationService->notify(
                (int) $profileId,
                'Alerta SLA de disputas',
                $this->buildAlertMessage($payload),
                'system',
                $payload
            );
        }
    }

    private function buildAlertMessage(array $payload): string
    {
        return sprintf(
            'Backlog de disputas envejecido (%s): >12h=%d, >24h=%d, >72h=%d.',
            strtoupper((string) ($payload['alert_level'] ?? 'warning')),
            (int) ($payload['pending_older_than_12h'] ?? 0),
            (int) ($payload['pending_older_than_24h'] ?? 0),
            (int) ($payload['pending_older_than_72h'] ?? 0)
        );
    }

    private function resolveAlertLevel(int $pending12h, int $pending24h, int $pending72h): ?string
    {
        if ($pending72h > 0) {
            return 'critical';
        }
        if ($pending24h >= 3) {
            return 'high';
        }
        if ($pending12h >= 5) {
            return 'warning';
        }

        return null;
    }

    private function pendingOlderThanHours(int $hours): int
    {
        return Dispute::whereIn('status', ['pending', 'in_review'])
            ->where('created_at', '<=', now()->subHours($hours))
            ->count();
    }
}
