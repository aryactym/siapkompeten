<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsContent extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    public const KATEGORI = [
        'slide_banner' => 'Slide Banner',
        'welcome' => 'Welcome',
        'layanan' => 'Layanan',
        'asesor' => 'Asesor',
        'portfolio' => 'Portfolio',
        'berita' => 'Berita',
        'hubungi_kami' => 'Hubungi Kami',
        'google_maps' => 'Google Maps',
        'form_login' => 'Form Login',
        'footer_menu' => 'Footer Menu',
    ];

    protected $fillable = [
        'institution_id',
        'kategori',
        'judul',
        'sub_judul',
        'tanggal_terbit',
        'jam_terbit',
        'isi',
        'gambar_path',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date',
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /**
     * Sanitasi HTML dengan allowlist: buang elemen berbahaya beserta isinya,
     * unwrap elemen tak dikenal, dan tolak URL berskema tidak aman.
     */
    public function setIsiAttribute(?string $value): void
    {
        $this->attributes['isi'] = HtmlSanitizer::clean($value);
    }
}
