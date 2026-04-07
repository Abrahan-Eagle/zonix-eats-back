<?php

namespace Tests\Feature;

use App\Models\Commerce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_link_returns_404_when_commerce_missing(): void
    {
        $response = $this->get('/r/999999');
        $response->assertNotFound();
    }

    public function test_storefront_link_returns_page_with_deep_link(): void
    {
        $commerce = Commerce::factory()->create();

        $response = $this->get('/r/'.$commerce->id);

        $response->assertOk();
        $response->assertSee($commerce->business_name, false);
        $response->assertSee('zonix://restaurant/'.$commerce->id, false);
    }
}
