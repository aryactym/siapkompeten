<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\InstitutionDocument;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_or_download_another_institution_document(): void
    {
        $lembagaA = Institution::create(['name' => 'Lembaga A', 'slug' => 'lembaga-a']);
        $lembagaB = Institution::create(['name' => 'Lembaga B', 'slug' => 'lembaga-b']);

        $userA = User::factory()->create(['institution_id' => $lembagaA->id]);
        $userA->institutions()->attach($lembagaA->id);

        $documentB = new InstitutionDocument([
            'title' => 'Dokumen B',
            'file_path' => 'uploads/'.$lembagaB->id.'/rahasia.pdf',
            'original_name' => 'rahasia.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10,
        ]);
        $documentB->institution_id = $lembagaB->id;
        $documentB->save();

        $this->actingAs($userA)
            ->withSession([TenantContext::SESSION_ID => $lembagaA->id])
            ->get(route('documents.show', $documentB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->withSession([TenantContext::SESSION_ID => $lembagaA->id])
            ->get(route('documents.download', $documentB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->withSession([TenantContext::SESSION_ID => $lembagaA->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($documentB->title);
    }

    public function test_upload_is_stored_in_the_active_institution_folder(): void
    {
        Storage::fake('public');

        $lembaga = Institution::create(['name' => 'Lembaga A', 'slug' => 'lembaga-a']);
        $user = User::factory()->create(['institution_id' => $lembaga->id]);
        $user->institutions()->attach($lembaga->id);

        $response = $this->actingAs($user)
            ->withSession([TenantContext::SESSION_ID => $lembaga->id])
            ->post(route('documents.store'), [
                'title' => 'Dokumen Unggahan',
                'file' => UploadedFile::fake()->create('dokumen.pdf', 32, 'application/pdf'),
            ]);

        $response->assertRedirect(route('dashboard'));

        $document = InstitutionDocument::withoutTenantScope()->firstOrFail();

        $this->assertSame($lembaga->id, $document->institution_id);
        $this->assertStringStartsWith('uploads/'.$lembaga->id.'/', $document->file_path);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_institution_id_is_auto_filled_and_immutable(): void
    {
        $lembaga = Institution::create(['name' => 'Lembaga A', 'slug' => 'lembaga-a']);
        $this->withSession([TenantContext::SESSION_ID => $lembaga->id]);

        $document = InstitutionDocument::create([
            'title' => 'Dokumen A',
            'file_path' => 'uploads/'.$lembaga->id.'/dokumen.pdf',
            'original_name' => 'dokumen.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10,
        ]);

        $this->assertSame($lembaga->id, $document->institution_id);

        $this->expectException(\LogicException::class);
        $document->institution_id = Institution::create(['name' => 'Lembaga B', 'slug' => 'lembaga-b'])->id;
        $document->save();
    }
}
