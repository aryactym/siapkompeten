<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tamu melihat landing publik, bukan diarahkan ke halaman login.
     */
    public function test_the_application_shows_public_landing_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
