<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\InstitutionDocument;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Single-tenant: menguji bahwa TenantContext selalu menyediakan institution aktif
 * dari database, institution_id di-autofill, dan immutable setelah disimpan.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'LSP Test',
            'slug' => 'lsp-test',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
    }

    public function test_tenant_context_returns_active_institution_from_database(): void
    {
        $institution = TenantContext::institution();

        $this->assertNotNull($institution);
        $this->assertSame($this->institution->id, $institution->id);
        $this->assertSame('LSP Test', $institution->name);
    }

    public function test_tenant_context_id_matches_active_institution(): void
    {
        $this->assertSame($this->institution->id, TenantContext::id());
    }

    public function test_institution_id_is_auto_filled_on_create(): void
    {
        $document = InstitutionDocument::create([
            'title' => 'Dokumen Test',
            'file_path' => 'uploads/'.$this->institution->id.'/dokumen.pdf',
            'original_name' => 'dokumen.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10,
        ]);

        $this->assertSame($this->institution->id, $document->institution_id);
    }

    public function test_institution_id_is_immutable_after_create(): void
    {
        $document = InstitutionDocument::create([
            'title' => 'Dokumen Immutable',
            'file_path' => 'uploads/'.$this->institution->id.'/file.pdf',
            'original_name' => 'file.pdf',
            'mime_type' => 'application/pdf',
            'size' => 5,
        ]);

        $other = Institution::create([
            'name' => 'LSP Lain',
            'slug' => 'lsp-lain',
            'is_active' => false,
        ]);

        $this->expectException(\LogicException::class);
        $document->institution_id = $other->id;
        $document->save();
    }

    public function test_document_query_is_scoped_to_active_institution(): void
    {
        InstitutionDocument::create([
            'title' => 'Dokumen A',
            'file_path' => 'uploads/'.$this->institution->id.'/a.pdf',
            'original_name' => 'a.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1,
        ]);

        // Dokumen yang di-query harus selalu milik institution aktif
        $documents = InstitutionDocument::all();
        $this->assertCount(1, $documents);
        $this->assertTrue($documents->every(
            fn ($d) => $d->institution_id === $this->institution->id
        ));
    }
}
