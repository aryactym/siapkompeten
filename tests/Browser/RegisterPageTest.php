<?php

namespace Tests\Browser;

use App\Models\Institution;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Tests\DuskTestCase;

class RegisterPageTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Institution::create([
            'name' => 'LSP Dusk Registrasi',
            'slug' => 'lsp-dusk-registrasi',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        Province::create(['code' => '32', 'name' => 'JAWA BARAT']);
        Province::create(['code' => '35', 'name' => 'JAWA TIMUR']);
        Province::create(['code' => '16', 'name' => 'SUMATERA SELATAN']);

        City::create(['code' => '3271', 'province_code' => '32', 'name' => 'KOTA BANDUNG']);
        City::create(['code' => '3272', 'province_code' => '32', 'name' => 'KOTA CIMAHI']);
        City::create(['code' => '3204', 'province_code' => '32', 'name' => 'KABUPATEN BANDUNG']);
    }

    public function test_register_page_layout_at_common_widths(): void
    {
        $this->browse(function (Browser $browser): void {
            foreach ([360, 768, 1280] as $width) {
                $browser->resize($width, 900)
                    ->visit('/daftar')
                    ->assertSee('Registrasi Asesi')
                    ->assertSee('Akun')
                    ->assertSee('Data Pribadi')
                    ->assertSee('Alamat')
                    ->assertSee('Pendidikan')
                    ->assertScript('return document.documentElement.scrollWidth <= document.documentElement.clientWidth;');
            }
        });
    }

    public function test_province_combobox_filters_and_privacy_link_exists(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/daftar')
                ->type('#provinsi_code_search', 'jawa barat')
                ->click('#provinsi_code_option_0')
                ->waitUntilEnabled('#kota_code_search', 5)
                ->type('#kota_code_search', 'Bandung')
                ->assertSeeIn('#kota_code_listbox', 'Bandung', true)
                ->assertInputValue('#provinsi_code_search', 'JAWA BARAT');

            $browser->visit('/daftar')
                ->type('#provinsi_code_search', 'jawa')
                ->assertSeeIn('#provinsi_code_listbox', 'Jawa Barat', true)
                ->assertSeeIn('#provinsi_code_listbox', 'Jawa Timur', true)
                ->assertAttribute('a[href$="/kebijakan-privasi"]', 'target', '_blank')
                ->assertAttribute('a[href$="/kebijakan-privasi"]', 'rel', 'noopener noreferrer');
        });
    }
}
