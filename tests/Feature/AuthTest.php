<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'LSP Uji Coba',
            'slug' => 'lsp-uji-coba',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'username' => 'budisantoso',
            'nik' => '3201123456780001',
            'password' => Hash::make('password123'),
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);

        $this->user->institutions()->attach($this->institution->id, ['role' => 'asesi']);
    }

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('SiapKompeten');
        $response->assertSee('Masuk ke Portal');
    }

    public function test_user_can_login_with_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'budi@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_can_login_with_16_digit_nik(): void
    {
        $response = $this->post('/login', [
            'login' => '3201123456780001',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'login' => 'budisantoso',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'login' => 'budi@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_blocked_user_cannot_login(): void
    {
        $this->user->update(['status' => 'diblokir']);

        $response = $this->post('/login', [
            'login' => 'budi@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_login_rate_limiting_after_excessive_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login' => 'budi@example.com',
                'password' => 'wrongpass',
            ]);
        }

        $response = $this->post('/login', [
            'login' => 'budi@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(429);
    }

    public function test_authenticated_user_can_lock_session(): void
    {
        $response = $this->actingAs($this->user)->get('/lock');

        $response->assertStatus(200);
        $response->assertSee('Sesi Terkunci');
        $response->assertSee('Budi Santoso');
        $this->assertTrue($this->user->fresh()->is_locked);
    }

    public function test_locked_user_is_redirected_to_lock_screen_from_dashboard(): void
    {
        $this->user->update(['is_locked' => true]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertRedirect('/lock');
    }

    public function test_locked_user_can_unlock_with_correct_password(): void
    {
        $this->user->update(['is_locked' => true]);

        $response = $this->actingAs($this->user)->post('/lock', [
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertFalse($this->user->fresh()->is_locked);
    }

    public function test_locked_user_cannot_unlock_with_wrong_password(): void
    {
        $this->user->update(['is_locked' => true]);

        $response = $this->actingAs($this->user)->post('/lock', [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertTrue($this->user->fresh()->is_locked);
    }

    public function test_user_can_logout(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
