<?php

namespace Tests\Feature;

use Tests\TestCase;

class PusherConfigTest extends TestCase
{
    /** @test */
    public function broadcast_driver_in_testing_is_null_to_avoid_real_pusher_http()
    {
        $driver = config('broadcasting.default');
        $this->assertTrue(
            $driver === null || $driver === 'null',
            'phpunit debe desactivar broadcasting (BROADCAST_DRIVER null) para no llamar a Pusher en la suite'
        );
    }

    /** @test */
    public function pusher_credentials_are_present_in_environment()
    {
        $this->assertNotEmpty(env('PUSHER_APP_ID'), 'PUSHER_APP_ID no está configurado');
        $this->assertNotEmpty(env('PUSHER_APP_KEY'), 'PUSHER_APP_KEY no está configurado');
        $this->assertNotEmpty(env('PUSHER_APP_SECRET'), 'PUSHER_APP_SECRET no está configurado');
        $this->assertNotEmpty(env('PUSHER_APP_CLUSTER'), 'PUSHER_APP_CLUSTER no está configurado');
    }

    /** @test */
    public function pusher_connection_options_match_configuration()
    {
        $connection = config('broadcasting.connections.pusher');

        $this->assertEquals(env('PUSHER_APP_KEY'), $connection['key']);
        $this->assertEquals(env('PUSHER_APP_SECRET'), $connection['secret']);
        $this->assertEquals(env('PUSHER_APP_ID'), $connection['app_id']);
        $this->assertEquals(env('PUSHER_APP_CLUSTER', 'mt1'), $connection['options']['cluster']);
    }
}
