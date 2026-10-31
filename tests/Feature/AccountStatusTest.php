<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_user_is_logged_out_and_redirected_to_login(): void
    {
        $institution = Institution::create([
            'name' => 'LSP Test',
            'slug' => 'lsp-test',
            'is_active' => true,
        ]);

        $user = User::factory()->blocked()->create([
            'institution_id' => $institution->id,
        ]);

        $user->institutions()->attach($institution->id, ['role' => 'asesi']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
        $response->assertSessionHasErrors(['login']);
    }

    public function test_inactive_user_is_logged_out_and_redirected_to_login(): void
    {
        $institution = Institution::create([
            'name' => 'LSP Test 2',
            'slug' => 'lsp-test-2',
            'is_active' => true,
        ]);

        $user = User::factory()->inactive()->create([
            'institution_id' => $institution->id,
        ]);

        $user->institutions()->attach($institution->id, ['role' => 'asesi']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
        $response->assertSessionHasErrors(['login']);
    }
}
