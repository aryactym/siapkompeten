<?php

namespace Tests\Feature;

use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Institution::create([
            'name' => 'LSP Registrasi',
            'slug' => 'lsp-registrasi',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
    }

    public function test_register_page_renders_grouped_form(): void
    {
        $response = $this->get('/daftar');

        $response->assertOk()
            ->assertSee('LSP Registrasi')
            ->assertSee('name="name"', false)
            ->assertSee('name="nik"', false)
            ->assertSee('name="provinsi_code"', false)
            ->assertSee('name="kota_code"', false)
            ->assertSee('id="section-akun"', false)
            ->assertSee('id="section-pribadi"', false)
            ->assertSee('id="section-alamat"', false)
            ->assertSee('id="section-pendidikan"', false)
            ->assertSee(route('kebijakan-privasi'), false);
    }

    public function test_privacy_policy_page_renders(): void
    {
        $this->get('/kebijakan-privasi')
            ->assertOk()
            ->assertSee('Kebijakan Privasi');
    }
}
