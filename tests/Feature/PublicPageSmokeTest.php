<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_renders_for_guest(): void
    {
        $this->get('/beranda')->assertStatus(200)->assertSee('Beranda');
    }
}
