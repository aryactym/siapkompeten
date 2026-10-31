<?php

namespace App\Models;

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
     * Sanitasi HTML sederhana: buang <script>, <iframe>, <object>, <embed> dan atribut on*.
     */
    public function setIsiAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['isi'] = null;

            return;
        }

        $clean = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $value);
        $clean = preg_replace('#<iframe\b[^>]*>.*?</iframe>#is', '', (string) $clean);
        $clean = preg_replace('#<object\b[^>]*>.*?</object>#is', '', (string) $clean);
        $clean = preg_replace('#<embed\b[^>]*/?>#is', '', (string) $clean);
        $clean = preg_replace('#\son\w+\s*=\s*"[^"]*"#i', '', (string) $clean);
        $clean = preg_replace("#\son\w+\s*=\s*'[^']*'#i", '', (string) $clean);

        $this->attributes['isi'] = $clean;
    }
}
