<?php

namespace Tests\Browser;

use App\Models\Institution;
use App\Models\InstitutionDocument;
use App\Models\User;
use App\Services\InstitutionStorage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TenantIsolationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_cannot_access_other_institution_data_or_files(): void
    {
        $lembagaA = Institution::create([
            'name' => 'LSP Lembaga A',
            'slug' => 'lsp-lembaga-a',
        ]);

        $lembagaB = Institution::create([
            'name' => 'LSP Lembaga B',
            'slug' => 'lsp-lembaga-b',
        ]);

        $userA = User::factory()->create([
            'institution_id' => $lembagaA->id,
            'email' => 'user.a@example.test',
            'password' => 'password',
        ]);
        $userA->institutions()->attach($lembagaA->id);

        $path = app(InstitutionStorage::class)->storeForInstitution(
            $lembagaB->id,
            UploadedFile::fake()->create('rahasia-lembaga-b.pdf', 16, 'application/pdf')
        );

        $documentB = new InstitutionDocument([
            'title' => 'Dokumen Rahasia Lembaga B',
            'file_path' => $path,
            'original_name' => 'rahasia-lembaga-b.pdf',
            'mime_type' => 'application/pdf',
            'size' => 16384,
        ]);
        $documentB->institution_id = $lembagaB->id;
        $documentB->save();

        try {
            $this->browse(function (Browser $browser) use ($lembagaA, $lembagaB, $documentB): void {
                $browser->visit('/login')
                    ->type('login', 'user.a@example.test')
                    ->type('password', 'password')
                    ->press('Masuk Sekarang')
                    ->waitForLocation('/dashboard')
                    ->assertSee($lembagaA->name)
                    ->assertDontSee($lembagaB->name)
                    ->assertDontSee($documentB->title)
                    ->visit('/documents/'.$documentB->id)
                    ->assertSee('Not Found')
                    ->visit('/documents/'.$documentB->id.'/download')
                    ->assertSee('Not Found');
            });
        } finally {
            Storage::disk('public')->deleteDirectory('uploads/'.$lembagaB->id);
        }
    }
}
