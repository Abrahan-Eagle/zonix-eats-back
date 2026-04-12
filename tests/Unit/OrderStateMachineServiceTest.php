<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\OrderStateMachineService;
use PHPUnit\Framework\TestCase;

class OrderStateMachineServiceTest extends TestCase
{
    private OrderStateMachineService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new OrderStateMachineService;
    }

    public function test_normalize_status_maps_legacy_aliases(): void
    {
        $this->assertSame('pending_payment', $this->svc->normalizeStatus('pending'));
        $this->assertSame('paid', $this->svc->normalizeStatus('confirmed'));
        $this->assertSame('processing', $this->svc->normalizeStatus('preparing'));
        $this->assertSame('shipped', $this->svc->normalizeStatus('on_way'));
    }

    public function test_is_valid_status_accepts_canonical_enum(): void
    {
        $this->assertTrue($this->svc->isValidStatus('pending_payment'));
        $this->assertTrue($this->svc->isValidStatus('delivered'));
        $this->assertFalse($this->svc->isValidStatus('invalid_status'));
    }

    public function test_can_transition_commerce_pending_to_paid(): void
    {
        $r = $this->svc->canTransition('commerce', 'pending_payment', 'paid');
        $this->assertTrue($r['allowed']);
        $this->assertSame(200, $r['http_status']);
    }

    public function test_can_transition_buyer_cannot_force_paid(): void
    {
        $r = $this->svc->canTransition('buyer', 'pending_payment', 'paid');
        $this->assertFalse($r['allowed']);
        $this->assertSame(409, $r['http_status']);
    }

    public function test_can_transition_idempotent_same_status(): void
    {
        $r = $this->svc->canTransition('commerce', 'paid', 'paid');
        $this->assertTrue($r['allowed']);
        $this->assertStringContainsString('idempot', strtolower($r['message']));
    }

    public function test_commerce_may_deliver_pickup_after_shipped_when_order_context_given(): void
    {
        $pickup = new Order(['delivery_type' => 'pickup']);
        $r = $this->svc->canTransition('commerce', 'shipped', 'delivered', $pickup);
        $this->assertTrue($r['allowed']);
        $this->assertSame(200, $r['http_status']);
    }

    public function test_commerce_cannot_deliver_delivery_order_after_shipped(): void
    {
        $delivery = new Order(['delivery_type' => 'delivery']);
        $r = $this->svc->canTransition('commerce', 'shipped', 'delivered', $delivery);
        $this->assertFalse($r['allowed']);
        $this->assertSame(409, $r['http_status']);
    }

    public function test_commerce_cannot_deliver_after_shipped_without_order_context(): void
    {
        $r = $this->svc->canTransition('commerce', 'shipped', 'delivered', null);
        $this->assertFalse($r['allowed']);
        $this->assertSame(409, $r['http_status']);
    }
}
