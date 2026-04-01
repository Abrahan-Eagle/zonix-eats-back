<?php

namespace Tests\Feature;

use App\Events\OrderPendingAssignment;
use App\Jobs\AutoAssignDeliveryJob;
use App\Jobs\AutoAssignTimeoutJob;
use App\Models\Commerce;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class AutoAssignJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeout_job_dispatches_pending_assignment_event_when_order_is_unassigned(): void
    {
        Event::fake([OrderPendingAssignment::class]);

        $company = DeliveryCompany::factory()->create();
        $commerce = Commerce::factory()->create(['open' => true]);
        $order = Order::factory()->create([
            'status' => 'shipped',
            'commerce_id' => $commerce->id,
            'delivery_company_id' => $company->id,
        ]);

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('notify')->once();

        (new AutoAssignTimeoutJob($order->id))->handle($notificationService);

        Event::assertDispatched(OrderPendingAssignment::class);
    }

    public function test_timeout_job_does_not_dispatch_event_when_order_is_already_assigned(): void
    {
        Event::fake([OrderPendingAssignment::class]);

        $company = DeliveryCompany::factory()->create();
        $commerce = Commerce::factory()->create(['open' => true]);
        $order = Order::factory()->create([
            'status' => 'shipped',
            'commerce_id' => $commerce->id,
            'delivery_company_id' => $company->id,
        ]);
        OrderDelivery::factory()->create([
            'order_id' => $order->id,
            'status' => 'assigned',
        ]);

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('notify')->never();

        (new AutoAssignTimeoutJob($order->id))->handle($notificationService);

        Event::assertNotDispatched(OrderPendingAssignment::class);
    }

    public function test_auto_assign_delivery_job_dispatches_pending_assignment_when_company_has_no_agents(): void
    {
        Event::fake([OrderPendingAssignment::class]);

        $company = DeliveryCompany::factory()->create();
        $commerce = Commerce::factory()->create(['open' => true]);
        $order = Order::factory()->create([
            'status' => 'processing',
            'commerce_id' => $commerce->id,
            'delivery_company_id' => $company->id,
        ]);

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('notify')->once();

        (new AutoAssignDeliveryJob($order->id))->handle($notificationService);

        Event::assertDispatched(OrderPendingAssignment::class);
    }
}
