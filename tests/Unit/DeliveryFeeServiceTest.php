<?php

namespace Tests\Unit;

use App\Services\DeliveryFeeService;
use PHPUnit\Framework\TestCase;

class DeliveryFeeServiceTest extends TestCase
{
    public function test_distance_km_same_point_is_zero(): void
    {
        $d = DeliveryFeeService::distanceKm(10.0, -68.0, 10.0, -68.0);
        $this->assertSame(0.0, $d);
    }

    public function test_distance_km_known_short_hop_is_positive(): void
    {
        $d = DeliveryFeeService::distanceKm(10.0, -68.0, 10.01, -68.0);
        $this->assertGreaterThan(0, $d);
        $this->assertLessThan(2.0, $d);
    }
}
