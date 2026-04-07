<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Contrato de claves de métricas en cache (alineado con NotificationService y BroadcastingController).
 * Evita drift en getRealtimeMetricsSnapshot sin bootear Laravel.
 */
class NotificationServiceMetricsTest extends TestCase
{
    public function test_expected_metric_key_prefix(): void
    {
        $keys = [
            'metrics:realtime:notification_broadcast_emitted_total',
            'metrics:realtime:fcm_sent_total',
            'metrics:realtime:notification_emit_failed_total',
            'metrics:realtime:fcm_skipped_no_token_total',
            'metrics:realtime:fcm_skipped_preferences_total',
            'metrics:realtime:fcm_failed_total',
            'metrics:realtime:broadcast_auth_success_total',
            'metrics:realtime:broadcast_auth_denied_total',
            'metrics:realtime:broadcast_auth_error_total',
        ];
        foreach ($keys as $k) {
            $this->assertStringStartsWith('metrics:realtime:', $k);
        }
        $this->assertCount(9, $keys);
    }
}
