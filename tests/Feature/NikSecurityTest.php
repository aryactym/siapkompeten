<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NikSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_nik_is_encrypted_in_database_and_blind_indexed(): void
    {
        $institution = Institution::create([
            'name' => 'LSP Nik Test',
            'slug' => 'lsp-nik-test',
            'is_active' => true,
        ]);

        $plainNik = '3201123456780001';

        $user = User::factory()->create([
            'institution_id' => $institution->id,
            'nik' => $plainNik,
        ]);

        // 1. Raw database value must NOT contain the plain NIK (OWASP A04)
        $rawUser = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotNull($rawUser->nik);
        $this->assertStringNotContainsString($plainNik, $rawUser->nik);

        // 2. Blind index nik_hash must match HMAC-SHA256
        $expectedHash = hash_hmac('sha256', $plainNik, (string) config('app.key'));
        $this->assertSame($expectedHash, $rawUser->nik_hash);

        // 3. Eloquent decrypts on read
        $freshUser = User::find($user->id);
        $this->assertSame($plainNik, $freshUser->nik);

        // 4. Masked NIK helper masks middle digits
        $this->assertSame('3201********0001', $freshUser->masked_nik);
    }
}
