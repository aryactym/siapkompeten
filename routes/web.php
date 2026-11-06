<?php

use App\Http\Controllers\Admin\AdminCmsController;
use App\Http\Controllers\Admin\AdminElemenKompetensiController;
use App\Http\Controllers\Admin\AdminInstitutionController;
use App\Http\Controllers\Admin\AdminKotaController;
use App\Http\Controllers\Admin\AdminPelaporanController;
use App\Http\Controllers\Admin\AdminProvinsiController;
use App\Http\Controllers\Admin\AdminSkemaController;
use App\Http\Controllers\Admin\AdminStkkniController;
use App\Http\Controllers\Admin\AdminTukController;
use App\Http\Controllers\Admin\AdminUnitKompetensiController;
use App\Http\Controllers\Admin\AdminUnitMappingController;
use App\Http\Controllers\Admin\AdminWilayahController;
use App\Http\Controllers\Admin\AsesiController as AdminAsesiController;
use App\Http\Controllers\Admin\AsesiImportController as AdminAsesiImportController;
use App\Http\Controllers\Admin\AsesorController as AdminAsesorController;
use App\Http\Controllers\Admin\AsesorInviteController as AdminAsesorInviteController;
use App\Http\Controllers\Admin\AsesorLingkupController as AdminAsesorLingkupController;
use App\Http\Controllers\Admin\BiayaController as AdminBiayaController;
use App\Http\Controllers\Admin\BundleSertifikatController as AdminBundleSertifikatController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\JadwalController as AdminJadwalController;
use App\Http\Controllers\Admin\NotifikasiController as AdminNotifikasiController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\PendaftaranController as AdminPendaftaranController;
use App\Http\Controllers\Admin\PlenoController as AdminPlenoController;
use App\Http\Controllers\Admin\RekeningBankController as AdminRekeningBankController;
use App\Http\Controllers\Admin\SertifikatController as AdminSertifikatController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ValidasiAsesmenController as AdminValidasiAsesmenController;
use App\Http\Controllers\Asesi\ActivationController as AsesiActivationController;
use App\Http\Controllers\Asesi\Apl02Controller as AsesiApl02Controller;
use App\Http\Controllers\Asesi\BandingController as AsesiBandingController;
use App\Http\Controllers\Asesi\PanduanController as AsesiPanduanController;
use App\Http\Controllers\Asesi\PembayaranController as AsesiPembayaranController;
use App\Http\Controllers\Asesi\PendaftaranController as AsesiPendaftaranController;
use App\Http\Controllers\Asesi\PersetujuanAsesmenController as AsesiPersetujuanAsesmenController;
use App\Http\Controllers\Asesi\ProfilController as AsesiProfilController;
use App\Http\Controllers\Asesi\SertifikatController as AsesiSertifikatController;
use App\Http\Controllers\Asesi\UmpanBalikController as AsesiUmpanBalikController;
use App\Http\Controllers\Asesor\Apl02Controller as AsesorApl02Controller;
use App\Http\Controllers\Asesor\BandingController as AsesorBandingController;
use App\Http\Controllers\Asesor\HasilAsesmenController as AsesorHasilAsesmenController;
use App\Http\Controllers\Asesor\Ia03Controller as AsesorIa03Controller;
use App\Http\Controllers\Asesor\Ia04Controller as AsesorIa04Controller;
use App\Http\Controllers\Asesor\Ia05Controller as AsesorIa05Controller;
use App\Http\Controllers\Asesor\Ia06Controller as AsesorIa06Controller;
use App\Http\Controllers\Asesor\Ia07Controller as AsesorIa07Controller;
use App\Http\Controllers\Asesor\Ia09Controller as AsesorIa09Controller;
use App\Http\Controllers\Asesor\Ia10Controller as AsesorIa10Controller;
use App\Http\Controllers\Asesor\InstrumenAsesmenController as AsesorInstrumenAsesmenController;
use App\Http\Controllers\Asesor\JadwalController as AsesorJadwalController;
use App\Http\Controllers\Asesor\LaporanAsesmenController as AsesorLaporanAsesmenController;
use App\Http\Controllers\Asesor\PersetujuanAsesmenController as AsesorPersetujuanAsesmenController;
use App\Http\Controllers\Asesor\PresensiController as AsesorPresensiController;
use App\Http\Controllers\Asesor\RencanaAsesmenController as AsesorRencanaAsesmenController;
use App\Http\Controllers\Asesor\TinjauanProsesController as AsesorTinjauanProsesController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SertifikatVerifikasiController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\WidgetController;
use App\Http\Controllers\WilayahController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return app(PublicController::class)->index();
})->name('home');

Route::get('/beranda', [PublicController::class, 'index'])->name('beranda');

// Verifikasi keaslian sertifikat (publik - tidak perlu auth) - FR-CMS-06
Route::get('/verifikasi-sertifikat', [SertifikatVerifikasiController::class, 'form'])
    ->middleware('throttle:30,1')
    ->name('sertifikat.form');
Route::post('/verifikasi-sertifikat', [SertifikatVerifikasiController::class, 'lookup'])
    ->middleware('throttle:10,1')
    ->name('sertifikat.lookup');
Route::get('/sertifikat/{token}', [SertifikatVerifikasiController::class, 'verify'])
    ->where('token', '[a-zA-Z0-9]{32}')
    ->name('sertifikat.verifikasi');

Route::get('/kebijakan-privasi', fn () => view('publik.kebijakan-privasi'))->name('kebijakan-privasi');

// Wilayah — endpoint publik untuk dependent select kota/provinsi
Route::get('/wilayah/provinsi/{code}/kota', [WilayahController::class, 'kotaByProvinsi'])
    ->middleware('throttle:60,1')
    ->name('wilayah.kota');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/daftar', [RegisterController::class, 'create'])
        ->middleware('throttle:10,1')
        ->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::get('/aktivasi/{user}', [AsesiActivationController::class, 'show'])
    ->middleware('signed')
    ->name('asesi.activate');
Route::post('/aktivasi/{user}', [AsesiActivationController::class, 'store'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('asesi.activate.store');

Route::middleware('auth')->group(function (): void {
    // GET /lock : tampilkan halaman lock (tetap ada untuk akses langsung via URL)
    Route::get('/lock', [LoginController::class, 'lock'])->name('lock');
    // POST /lock/aktivasi : kunci layar via form POST (aman, tidak via GET)
    Route::post('/lock/aktivasi', [LoginController::class, 'lock'])->name('lock.lock');
    Route::post('/lock', [LoginController::class, 'unlock'])->name('lock.unlock');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    // Ganti portal aktif tanpa login ulang (multi-role: asesor sekaligus asesi)
    Route::post('/portal/switch', [LoginController::class, 'switchPortal'])->name('portal.switch');
});

Route::middleware(['auth', 'tenant.context'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/widgets/{key}', [WidgetController::class, 'show'])->name('dashboard.widget');

    Route::get('/documents/{document}', [DocumentController::class, 'show'])
        ->name('documents.show');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function (): void {
        Route::redirect('/', '/dashboard')->name('dashboard');

        // Master Standar (Fase 1)
        Route::resource('stkkni', AdminStkkniController::class)->except(['show']);
        Route::get('stkkni/{stkkni}', [AdminStkkniController::class, 'show'])->name('stkkni.show');
        Route::resource('skema', AdminSkemaController::class)->except(['show']);
        Route::get('skema/{skema}', [AdminSkemaController::class, 'show'])->name('skema.show');
        Route::get('skema/{skema}/persyaratan', [AdminSkemaController::class, 'persyaratan'])
            ->name('skema.persyaratan');
        Route::post('skema/{skema}/persyaratan', [AdminSkemaController::class, 'storePersyaratan'])
            ->name('skema.persyaratan.store');
        Route::get('skema/{skema}/persyaratan/{persyaratan}/edit', [AdminSkemaController::class, 'editPersyaratan'])
            ->name('skema.persyaratan.edit');
        Route::put('skema/{skema}/persyaratan/{persyaratan}', [AdminSkemaController::class, 'updatePersyaratan'])
            ->name('skema.persyaratan.update');
        Route::delete('skema/{skema}/persyaratan/{persyaratan}', [AdminSkemaController::class, 'destroyPersyaratan'])
            ->name('skema.persyaratan.destroy');
        Route::resource('unit', AdminUnitKompetensiController::class)->except(['show']);
        Route::get('unit/{unit}', [AdminUnitKompetensiController::class, 'show'])->name('unit.show');
        Route::resource('unit-mappings', AdminUnitMappingController::class)->except(['show']);
        Route::resource('elemen', AdminElemenKompetensiController::class)->except(['show']);
        Route::post('elemen/{elemen}/kuk', [AdminElemenKompetensiController::class, 'storeKuk'])
            ->name('elemen.kuk.store');
        Route::delete('kuk/{kuk}', [AdminElemenKompetensiController::class, 'destroyKuk'])
            ->name('kuk.destroy');
        Route::resource('provinces', AdminProvinsiController::class)->except(['show']);
        Route::resource('cities', AdminKotaController::class)->except(['show']);
        Route::resource('wilayah', AdminWilayahController::class)->except(['show']);

        // Kelembagaan (Fase 1)
        Route::resource('institution', AdminInstitutionController::class);
        Route::post('institution/{institution}/logo', [AdminInstitutionController::class, 'uploadLogo'])
            ->name('institution.upload-logo');
        Route::post('institution/{institution}/signature', [AdminInstitutionController::class, 'uploadSignature'])
            ->middleware('throttle:10,1')
            ->name('institution.upload-signature');
        Route::delete('institution/{institution}/signature', [AdminInstitutionController::class, 'deleteSignature'])
            ->middleware('throttle:10,1')
            ->name('institution.delete-signature');
        Route::resource('tuk', AdminTukController::class)->except(['show']);

        Route::resource('event', AdminEventController::class)->except(['show']);

        Route::get('jadwal', [AdminJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('jadwal/create', [AdminJadwalController::class, 'create'])->name('jadwal.create');
        Route::post('jadwal', [AdminJadwalController::class, 'store'])->name('jadwal.store');
        Route::get('jadwal/{jadwal}', [AdminJadwalController::class, 'show'])->name('jadwal.show');
        Route::get('jadwal/{jadwal}/edit', [AdminJadwalController::class, 'edit'])->name('jadwal.edit');
        Route::put('jadwal/{jadwal}', [AdminJadwalController::class, 'update'])->name('jadwal.update');
        Route::delete('jadwal/{jadwal}', [AdminJadwalController::class, 'destroy'])->name('jadwal.destroy');
        Route::post('jadwal/{jadwal}/asesor', [AdminJadwalController::class, 'assignAsesor'])->name('jadwal.asesor.store');
        Route::delete('jadwal/{jadwal}/asesor/{jadwalAsesor}', [AdminJadwalController::class, 'removeAsesor'])->name('jadwal.asesor.destroy');
        Route::get('jadwal/{jadwal}/presensi', [AdminJadwalController::class, 'presensi'])->name('jadwal.presensi');
        Route::get('jadwal/{jadwal}/laporan-asesmen/pdf', [AsesorLaporanAsesmenController::class, 'pdf'])->name('jadwal.laporan-asesmen.pdf');
        Route::get('jadwal/{jadwal}/tinjauan-proses/pdf', [AsesorTinjauanProsesController::class, 'pdf'])->name('jadwal.tinjauan-proses.pdf');
        Route::get('jadwal/{jadwal}/surat-tugas/{jadwalAsesor}', [AdminJadwalController::class, 'suratTugas'])->name('jadwal.surat-tugas');

        Route::get('asesor', [AdminAsesorController::class, 'index'])->name('asesor.index');
        Route::get('asesor/create', [AdminAsesorController::class, 'create'])->name('asesor.create');
        Route::post('asesor', [AdminAsesorController::class, 'store'])->name('asesor.store');
        Route::get('asesor/{asesor}/edit', [AdminAsesorController::class, 'edit'])->name('asesor.edit');
        Route::put('asesor/{asesor}', [AdminAsesorController::class, 'update'])->name('asesor.update');

        Route::get('asesi', [AdminAsesiController::class, 'index'])->name('asesi.index');
        Route::get('asesi/create', [AdminAsesiController::class, 'create'])->name('asesi.create');
        Route::post('asesi', [AdminAsesiController::class, 'store'])->name('asesi.store');

        Route::get('asesi/impor', [AdminAsesiImportController::class, 'create'])->name('asesi.import.create');
        Route::get('asesi/impor/template', [AdminAsesiImportController::class, 'template'])->name('asesi.import.template');
        Route::post('asesi/impor/preview', [AdminAsesiImportController::class, 'preview'])
            ->middleware('throttle:10,1')
            ->name('asesi.import.preview');
        Route::get('asesi/impor/{batch}', [AdminAsesiImportController::class, 'show'])->name('asesi.import.show');
        Route::get('asesi/impor/{batch}/laporan', [AdminAsesiImportController::class, 'report'])->name('asesi.import.report');
        Route::post('asesi/impor/{batch}', [AdminAsesiImportController::class, 'commit'])
            ->middleware('throttle:5,1')
            ->name('asesi.import.commit');
        Route::delete('asesi/impor/{batch}', [AdminAsesiImportController::class, 'destroy'])->name('asesi.import.destroy');

        Route::get('asesi/{asesi}', [AdminAsesiController::class, 'show'])->name('asesi.show');
        Route::get('asesi/{asesi}/edit', [AdminAsesiController::class, 'edit'])->name('asesi.edit');
        Route::put('asesi/{asesi}', [AdminAsesiController::class, 'update'])->name('asesi.update');
        Route::post('asesi/{asesi}/verifikasi', [AdminAsesiController::class, 'verify'])->name('asesi.verify');
        Route::post('asesi/{asesi}/pendaftaran', [AdminAsesiController::class, 'storePendaftaran'])->name('asesi.pendaftaran.store');
        Route::post('asesi/{asesi}/password', [AdminAsesiController::class, 'setPassword'])->name('asesi.password.set');

        Route::get('pendaftaran/dokumen/{dokumen}/lihat', [AdminPendaftaranController::class, 'previewDokumen'])->name('pendaftaran.dokumen.preview');
        Route::get('pendaftaran/dokumen/{dokumen}/unduh', [AdminPendaftaranController::class, 'downloadDokumen'])->name('pendaftaran.dokumen.download');
        Route::get('pendaftaran', [AdminPendaftaranController::class, 'index'])->name('pendaftaran.index');
        Route::get('pendaftaran/{pendaftaran}', [AdminPendaftaranController::class, 'show'])->name('pendaftaran.show');
        Route::get('pendaftaran/{pendaftaran}/apl01', [AdminPendaftaranController::class, 'apl01'])->name('pendaftaran.apl01');
        Route::get('pendaftaran/{pendaftaran}/mak01', [AdminPendaftaranController::class, 'mak01'])->name('pendaftaran.mak01');
        Route::get('pendaftaran/{pendaftaran}/mak03', [AdminPendaftaranController::class, 'mak03'])->name('pendaftaran.mak03');
        Route::post('pendaftaran/{pendaftaran}/dokumen/{dokumen}/verifikasi', [AdminPendaftaranController::class, 'verifyDokumen'])->name('pendaftaran.dokumen.verify');
        Route::get('pendaftaran/{pendaftaran}/verifikasi-semua', [AdminPendaftaranController::class, 'verifyAllDocumentsFallback'])->name('pendaftaran.verify-all.fallback');
        Route::post('pendaftaran/{pendaftaran}/verifikasi-semua', [AdminPendaftaranController::class, 'verifyAllDocuments'])->name('pendaftaran.verify-all');
        Route::post('pendaftaran/{pendaftaran}/setujui', [AdminPendaftaranController::class, 'approve'])->name('pendaftaran.approve');
        Route::post('pendaftaran/{pendaftaran}/tolak', [AdminPendaftaranController::class, 'reject'])->name('pendaftaran.reject');
        Route::post('pendaftaran/{pendaftaran}/presensi', [AdminPendaftaranController::class, 'catatPresensi'])
            ->middleware('throttle:30,1')
            ->name('pendaftaran.presensi.catat');
        Route::get('pendaftaran/{pendaftaran}/banding/pdf', [AsesorBandingController::class, 'pdf'])->name('pendaftaran.banding.pdf');
        Route::get('pendaftaran/{pendaftaran}/umpan-balik/pdf', [AsesiUmpanBalikController::class, 'pdf'])
            ->name('pendaftaran.umpan-balik.pdf');

        Route::get('pelaporan', [AdminPelaporanController::class, 'index'])->name('pelaporan.index');
        Route::get('pelaporan/export', [AdminPelaporanController::class, 'export'])->name('pelaporan.export');
        Route::get('pelaporan/apl02', [AdminPelaporanController::class, 'apl02'])->name('pelaporan.apl02');
        Route::get('pelaporan/bnsp', [AdminPelaporanController::class, 'bnsp'])->name('pelaporan.bnsp');
        Route::get('pelaporan/bnsp/pdf', [AdminPelaporanController::class, 'bnspPdf'])->name('pelaporan.bnsp.pdf');
        Route::get('notifikasi/keluar', [AdminNotifikasiController::class, 'index'])->name('notifikasi.index');
        Route::post('notifikasi/{notifikasi}/kirim-ulang', [AdminNotifikasiController::class, 'retry'])->name('notifikasi.retry');

        Route::resource('rekening-bank', AdminRekeningBankController::class)
            ->except(['show', 'create', 'edit'])
            ->names('rekening');

        Route::resource('biaya', AdminBiayaController::class)->except(['show']);

        Route::resource('cms', AdminCmsController::class)->except(['show']);

        Route::resource('pembayaran', AdminPembayaranController::class)->except(['create', 'edit', 'store']);
        Route::get('pembayaran/{pembayaran}/bukti', [AdminPembayaranController::class, 'bukti'])
            ->name('pembayaran.bukti');
        Route::post('pembayaran/{pembayaran}/putuskan', [AdminPembayaranController::class, 'putuskan'])
            ->middleware('throttle:30,1')
            ->name('pembayaran.putuskan');
        Route::post('pembayaran/{pendaftaran}/input-manual', [AdminPembayaranController::class, 'inputManual'])
            ->middleware('throttle:10,1')
            ->name('pembayaran.input-manual');

        // Tanda tangan untuk dokumen resmi LSP
        Route::get('tanda-tangan', [SignatureController::class, 'show'])->name('signature.show');
        Route::post('tanda-tangan', [SignatureController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('signature.store');
        Route::delete('tanda-tangan', [SignatureController::class, 'delete'])
            ->middleware('throttle:10,1')
            ->name('signature.delete');

        // Manajemen Pengguna
        Route::resource('user', AdminUserController::class)->except(['show']);

        // Dokumen Lembaga (dipindahkan dari route global untuk memastikan prefix /admin)
        Route::post('documents', [DocumentController::class, 'store'])
            ->name('documents.store');

        // Lingkup Kompetensi Asesor (FR-ASR-10)
        Route::get('asesor-skema', [AdminAsesorLingkupController::class, 'index'])->name('asesor.skema.index');
        Route::get('asesor/{asesor}/skema', [AdminAsesorLingkupController::class, 'show'])->name('asesor.skema.show');
        Route::post('asesor/{asesor}/skema', [AdminAsesorLingkupController::class, 'store'])->name('asesor.skema.store');
        Route::delete('asesor/{asesor}/skema/{kompetensi}', [AdminAsesorLingkupController::class, 'destroy'])->name('asesor.skema.destroy');

        // Undang Keanggotaan Asesor (FR-ASR-09)
        Route::get('asesor-undangan', [AdminAsesorInviteController::class, 'index'])->name('asesor.invite.index');
        Route::get('asesor-undangan/create', [AdminAsesorInviteController::class, 'create'])->name('asesor.invite.create');
        Route::post('asesor-undangan', [AdminAsesorInviteController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('asesor.invite.store');
        Route::get('asesor-undangan/{invitation}', [AdminAsesorInviteController::class, 'show'])->name('asesor.invite.show');
        Route::delete('asesor-undangan/{invitation}', [AdminAsesorInviteController::class, 'destroy'])->name('asesor.invite.destroy');

        // Sertifikat Kompetensi (FR-RPT-04)
        Route::get('sertifikat', [AdminSertifikatController::class, 'index'])->name('sertifikat.index');
        Route::post('sertifikat', [AdminSertifikatController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('sertifikat.store');
        Route::get('sertifikat/{sertifikat}', [AdminSertifikatController::class, 'show'])->name('sertifikat.show');
        Route::get('sertifikat/{sertifikat}/pdf', [AdminSertifikatController::class, 'pdf'])->name('sertifikat.pdf');

        // Bundle Sertifikat per Jadwal
        Route::get('sertifikat-bundle', [AdminBundleSertifikatController::class, 'index'])->name('sertifikat.bundle.index');
        Route::get('sertifikat-bundle/{jadwal}', [AdminBundleSertifikatController::class, 'create'])->name('sertifikat.bundle.create');
        Route::post('sertifikat-bundle/{jadwal}', [AdminBundleSertifikatController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('sertifikat.bundle.store');
    });

Route::prefix('validasi')
    ->name('validasi.')
    ->middleware(['auth', 'role:komite-teknis,admin,super-admin,manajer-sertifikasi'])
    ->group(function (): void {
        Route::get('pendaftaran/{pendaftaran}', [AdminValidasiAsesmenController::class, 'show'])->name('show');
        Route::post('pendaftaran/{pendaftaran}', [AdminValidasiAsesmenController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('store');
        Route::post('pendaftaran/{pendaftaran}/final', [AdminValidasiAsesmenController::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('final');
        Route::get('pendaftaran/{pendaftaran}/pdf', [AdminValidasiAsesmenController::class, 'pdf'])->name('pdf');
    });

Route::prefix('pleno')
    ->name('pleno.')
    ->middleware(['auth', 'role:komite-teknis,admin,super-admin,manajer-sertifikasi'])
    ->group(function (): void {
        Route::get('/', [AdminPlenoController::class, 'index'])->name('index');
        Route::get('jadwal/{jadwal}', [AdminPlenoController::class, 'show'])->name('show');
        Route::post('jadwal/{jadwal}', [AdminPlenoController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('store');
        Route::post('jadwal/{jadwal}/final', [AdminPlenoController::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('final');
        Route::get('jadwal/{jadwal}/pdf', [AdminPlenoController::class, 'pdf'])->name('pdf');
    });

Route::prefix('asesor')
    ->name('asesor.')
    ->middleware(['auth', 'role:asesor'])
    ->group(function (): void {
        Route::get('jadwal', [AsesorJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('jadwal/calendar', [AsesorJadwalController::class, 'calendar'])->name('jadwal.calendar');
        Route::get('jadwal/kalender', [AsesorJadwalController::class, 'calendar'])->name('jadwal.kalender');
        Route::get('jadwal/{jadwal}/peserta', [AsesorJadwalController::class, 'peserta'])->name('jadwal.peserta');
        Route::get('jadwal/{jadwal}/laporan-asesmen', [AsesorLaporanAsesmenController::class, 'show'])->name('laporan-asesmen.show');
        Route::post('jadwal/{jadwal}/laporan-asesmen', [AsesorLaporanAsesmenController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('laporan-asesmen.store');
        Route::post('jadwal/{jadwal}/laporan-asesmen/final', [AsesorLaporanAsesmenController::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('laporan-asesmen.final');
        Route::get('jadwal/{jadwal}/laporan-asesmen/pdf', [AsesorLaporanAsesmenController::class, 'pdf'])->name('laporan-asesmen.pdf');
        Route::get('jadwal/{jadwal}/tinjauan-proses', [AsesorTinjauanProsesController::class, 'show'])->name('tinjauan-proses.show');
        Route::post('jadwal/{jadwal}/tinjauan-proses', [AsesorTinjauanProsesController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('tinjauan-proses.store');
        Route::post('jadwal/{jadwal}/tinjauan-proses/final', [AsesorTinjauanProsesController::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('tinjauan-proses.final');
        Route::get('jadwal/{jadwal}/tinjauan-proses/pdf', [AsesorTinjauanProsesController::class, 'pdf'])->name('tinjauan-proses.pdf');
        Route::get('tanda-tangan', [SignatureController::class, 'show'])->name('signature.show');
        Route::post('tanda-tangan', [SignatureController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('signature.store');
        Route::delete('tanda-tangan', [SignatureController::class, 'delete'])
            ->middleware('throttle:10,1')
            ->name('signature.delete');

        Route::get('pendaftaran/{pendaftaran}/apl02', [AsesorApl02Controller::class, 'show'])->name('apl02.show');
        Route::post('pendaftaran/{pendaftaran}/apl02/verifikasi', [AsesorApl02Controller::class, 'verifikasi'])
            ->middleware('throttle:30,1')
            ->name('apl02.verifikasi');

        Route::get('pendaftaran/{pendaftaran}/mak02', [AsesorHasilAsesmenController::class, 'show'])->name('mak02.show');
        Route::post('pendaftaran/{pendaftaran}/mak02', [AsesorHasilAsesmenController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('mak02.store');
        Route::post('pendaftaran/{pendaftaran}/mak02/final', [AsesorHasilAsesmenController::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('mak02.final');
        Route::get('pendaftaran/{pendaftaran}/mak02/pdf', [AsesorHasilAsesmenController::class, 'pdf2'])->name('mak02.pdf');
        Route::get('pendaftaran/{pendaftaran}/mak02/pdf2', [AsesorHasilAsesmenController::class, 'pdf'])->name('mak02.pdf2');
        Route::get('pendaftaran/{pendaftaran}/mak02/pdf-redesign', [AsesorHasilAsesmenController::class, 'pdfRedesign'])->name('mak02.pdf-redesign');

        Route::get('pendaftaran/{pendaftaran}/pre-asesmen', [AsesorPersetujuanAsesmenController::class, 'show'])->name('pre-asesmen.show');
        Route::get('pendaftaran/{pendaftaran}/pre-asesmen/pdf', [AsesorPersetujuanAsesmenController::class, 'pdfGabungan'])->name('pre-asesmen.pdf.gabungan');
        Route::get('pendaftaran/{pendaftaran}/pre-asesmen/{jenis}/pdf', function ($pendaftaran, $jenis) {
            return redirect()->route('asesor.pre-asesmen.pdf.gabungan', $pendaftaran);
        })->name('pre-asesmen.pdf');

        Route::get('pendaftaran/{pendaftaran}/instrumen/{jenis}', [AsesorInstrumenAsesmenController::class, 'show'])
            ->name('instrumen.show');
        Route::post('pendaftaran/{pendaftaran}/instrumen/{jenis}', [AsesorInstrumenAsesmenController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('instrumen.store');
        Route::post('pendaftaran/{pendaftaran}/instrumen/{jenis}/final', [AsesorInstrumenAsesmenController::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('instrumen.final');
        Route::get('pendaftaran/{pendaftaran}/instrumen/{jenis}/pdf', [AsesorInstrumenAsesmenController::class, 'pdf'])
            ->name('instrumen.pdf');

        Route::get('pendaftaran/{pendaftaran}/ia03', [AsesorIa03Controller::class, 'show'])->name('ia03.show');
        Route::post('pendaftaran/{pendaftaran}/ia03', [AsesorIa03Controller::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('ia03.store');
        Route::post('pendaftaran/{pendaftaran}/ia03/pertanyaan', [AsesorIa03Controller::class, 'tambah'])
            ->middleware('throttle:30,1')
            ->name('ia03.tambah');
        Route::delete('pendaftaran/{pendaftaran}/ia03/pertanyaan/{item}', [AsesorIa03Controller::class, 'hapus'])
            ->name('ia03.hapus');
        Route::post('pendaftaran/{pendaftaran}/ia03/final', [AsesorIa03Controller::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('ia03.final');
        Route::get('pendaftaran/{pendaftaran}/ia03/pdf', [AsesorIa03Controller::class, 'pdf'])->name('ia03.pdf');

        Route::get('pendaftaran/{pendaftaran}/ia04', [AsesorIa04Controller::class, 'show'])->name('ia04.show');
        Route::post('pendaftaran/{pendaftaran}/ia04', [AsesorIa04Controller::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('ia04.store');
        Route::post('pendaftaran/{pendaftaran}/ia04/pertanyaan', [AsesorIa04Controller::class, 'tambah'])
            ->middleware('throttle:30,1')
            ->name('ia04.tambah');
        Route::delete('pendaftaran/{pendaftaran}/ia04/pertanyaan/{item}', [AsesorIa04Controller::class, 'hapus'])
            ->name('ia04.hapus');
        Route::post('pendaftaran/{pendaftaran}/ia04/final', [AsesorIa04Controller::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('ia04.final');
        Route::get('pendaftaran/{pendaftaran}/ia04/pdf', [AsesorIa04Controller::class, 'pdf'])->name('ia04.pdf');

        Route::get('pendaftaran/{pendaftaran}/ia05', [AsesorIa05Controller::class, 'show'])->name('ia05.show');
        Route::post('pendaftaran/{pendaftaran}/ia05', [AsesorIa05Controller::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('ia05.store');
        Route::post('pendaftaran/{pendaftaran}/ia05/soal', [AsesorIa05Controller::class, 'tambah'])
            ->middleware('throttle:30,1')
            ->name('ia05.tambah');
        Route::delete('pendaftaran/{pendaftaran}/ia05/soal/{soal}', [AsesorIa05Controller::class, 'hapus'])
            ->name('ia05.hapus');
        Route::post('pendaftaran/{pendaftaran}/ia05/final', [AsesorIa05Controller::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('ia05.final');
        Route::get('pendaftaran/{pendaftaran}/ia05/pdf', [AsesorIa05Controller::class, 'pdf'])->name('ia05.pdf');

        Route::get('pendaftaran/{pendaftaran}/ia06', [AsesorIa06Controller::class, 'show'])->name('ia06.show');
        Route::post('pendaftaran/{pendaftaran}/ia06', [AsesorIa06Controller::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('ia06.store');
        Route::post('pendaftaran/{pendaftaran}/ia06/soal', [AsesorIa06Controller::class, 'tambah'])
            ->middleware('throttle:30,1')
            ->name('ia06.tambah');
        Route::delete('pendaftaran/{pendaftaran}/ia06/soal/{soal}', [AsesorIa06Controller::class, 'hapus'])
            ->name('ia06.hapus');
        Route::post('pendaftaran/{pendaftaran}/ia06/final', [AsesorIa06Controller::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('ia06.final');
        Route::get('pendaftaran/{pendaftaran}/ia06/pdf', [AsesorIa06Controller::class, 'pdf'])->name('ia06.pdf');

        Route::get('pendaftaran/{pendaftaran}/ia07', [AsesorIa07Controller::class, 'show'])->name('ia07.show');
        Route::post('pendaftaran/{pendaftaran}/ia07', [AsesorIa07Controller::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('ia07.store');
        Route::post('pendaftaran/{pendaftaran}/ia07/pertanyaan', [AsesorIa07Controller::class, 'tambah'])
            ->middleware('throttle:30,1')
            ->name('ia07.tambah');
        Route::delete('pendaftaran/{pendaftaran}/ia07/pertanyaan/{item}', [AsesorIa07Controller::class, 'hapus'])
            ->name('ia07.hapus');
        Route::post('pendaftaran/{pendaftaran}/ia07/final', [AsesorIa07Controller::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('ia07.final');
        Route::get('pendaftaran/{pendaftaran}/ia07/pdf', [AsesorIa07Controller::class, 'pdf'])->name('ia07.pdf');

        Route::get('pendaftaran/{pendaftaran}/ia09', [AsesorIa09Controller::class, 'show'])->name('ia09.show');
        Route::post('pendaftaran/{pendaftaran}/ia09', [AsesorIa09Controller::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('ia09.store');
        Route::post('pendaftaran/{pendaftaran}/ia09/pertanyaan', [AsesorIa09Controller::class, 'tambah'])
            ->middleware('throttle:30,1')
            ->name('ia09.tambah');
        Route::delete('pendaftaran/{pendaftaran}/ia09/pertanyaan/{item}', [AsesorIa09Controller::class, 'hapus'])
            ->name('ia09.hapus');
        Route::post('pendaftaran/{pendaftaran}/ia09/final', [AsesorIa09Controller::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('ia09.final');
        Route::get('pendaftaran/{pendaftaran}/ia09/pdf', [AsesorIa09Controller::class, 'pdf'])->name('ia09.pdf');

        Route::get('pendaftaran/{pendaftaran}/ia10', [AsesorIa10Controller::class, 'show'])->name('ia10.show');
        Route::post('pendaftaran/{pendaftaran}/ia10', [AsesorIa10Controller::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('ia10.store');
        Route::post('pendaftaran/{pendaftaran}/ia10/final', [AsesorIa10Controller::class, 'final'])
            ->middleware('throttle:10,1')
            ->name('ia10.final');
        Route::get('pendaftaran/{pendaftaran}/ia10/pdf', [AsesorIa10Controller::class, 'pdf'])->name('ia10.pdf');

        Route::get('pendaftaran/{pendaftaran}/umpan-balik/pdf', [AsesiUmpanBalikController::class, 'pdf'])
            ->name('umpan-balik.pdf');

        Route::get('pendaftaran/{pendaftaran}/banding', [AsesorBandingController::class, 'show'])->name('banding.show');
        Route::post('pendaftaran/{pendaftaran}/banding/review', [AsesorBandingController::class, 'review'])
            ->middleware('throttle:20,1')
            ->name('banding.review');
        Route::get('pendaftaran/{pendaftaran}/banding/pdf', [AsesorBandingController::class, 'pdf'])->name('banding.pdf');

        Route::post('pendaftaran/{pendaftaran}/presensi', [AsesorPresensiController::class, 'catat'])
            ->middleware('throttle:30,1')
            ->name('presensi.catat');
        Route::post('pendaftaran/{pendaftaran}/mulai-asesmen', [AsesorPresensiController::class, 'mulai'])
            ->middleware('throttle:10,1')
            ->name('pendaftaran.mulai-asesmen');

        Route::get('rencana-asesmen', [AsesorRencanaAsesmenController::class, 'index'])->name('rencana-asesmen.index');
        Route::get('pendaftaran/{pendaftaran}/rencana-asesmen/create', [AsesorRencanaAsesmenController::class, 'create'])->name('rencana-asesmen.create');
        Route::post('pendaftaran/{pendaftaran}/rencana-asesmen', [AsesorRencanaAsesmenController::class, 'store'])->name('rencana-asesmen.store');
        Route::get('rencana-asesmen/{rencana}', [AsesorRencanaAsesmenController::class, 'edit'])->name('rencana-asesmen.edit');
        Route::put('rencana-asesmen/{rencana}', [AsesorRencanaAsesmenController::class, 'update'])->name('rencana-asesmen.update');
        Route::post('rencana-asesmen/{rencana}/final', [AsesorRencanaAsesmenController::class, 'finalize'])->name('rencana-asesmen.final');
        Route::get('rencana-asesmen/{rencana}/pdf', [AsesorRencanaAsesmenController::class, 'pdf'])->name('rencana-asesmen.pdf');
        Route::get('rencana-asesmen/{rencana}/pdf/mapa02', [AsesorRencanaAsesmenController::class, 'pdfMapa02'])->name('rencana-asesmen.pdf.mapa02');
    });

Route::prefix('asesi')
    ->name('asesi.')
    ->middleware(['auth', 'role:asesi'])
    ->group(function (): void {
        Route::get('profil', [AsesiProfilController::class, 'edit'])->name('profil.edit');
        Route::put('profil', [AsesiProfilController::class, 'update'])->name('profil.update');
        Route::get('tanda-tangan', [SignatureController::class, 'show'])->name('signature.show');
        Route::post('tanda-tangan', [SignatureController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('signature.store');
        Route::delete('tanda-tangan', [SignatureController::class, 'delete'])
            ->middleware('throttle:10,1')
            ->name('signature.delete');
        Route::get('panduan', AsesiPanduanController::class)->name('panduan');

        // Hasil & Sertifikat (FR-RPT-04)
        Route::get('sertifikat', [AsesiSertifikatController::class, 'index'])->name('sertifikat.index');
        Route::get('sertifikat/{sertifikat}', [AsesiSertifikatController::class, 'show'])->name('sertifikat.show');
        Route::get('sertifikat/{sertifikat}/pdf', [AsesiSertifikatController::class, 'pdf'])->name('sertifikat.pdf');

        Route::get('pendaftaran', [AsesiPendaftaranController::class, 'index'])->name('pendaftaran.index');
        Route::get('pendaftaran/create', [AsesiPendaftaranController::class, 'create'])->name('pendaftaran.create');
        Route::post('pendaftaran', [AsesiPendaftaranController::class, 'store'])->name('pendaftaran.store');
        Route::get('pendaftaran/{pendaftaran}', [AsesiPendaftaranController::class, 'show'])->name('pendaftaran.show');
        Route::get('pendaftaran/{pendaftaran}/pembayaran', [AsesiPembayaranController::class, 'show'])->name('pembayaran.show');
        Route::post('pendaftaran/{pendaftaran}/pembayaran', [AsesiPembayaranController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('pembayaran.store');
        Route::get('pendaftaran/{pendaftaran}/pembayaran/bukti', [AsesiPembayaranController::class, 'bukti'])->name('pembayaran.bukti');

        Route::get('pendaftaran/{pendaftaran}/apl02', [AsesiApl02Controller::class, 'show'])->name('apl02.show');
        Route::post('pendaftaran/{pendaftaran}/apl02', [AsesiApl02Controller::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('apl02.store');
        Route::post('pendaftaran/{pendaftaran}/apl02/serah', [AsesiApl02Controller::class, 'serah'])
            ->middleware('throttle:10,1')
            ->name('apl02.serah');
        Route::get('pendaftaran/{pendaftaran}/apl02/pdf', [AsesiApl02Controller::class, 'pdf'])->name('apl02.pdf');

        Route::get('pendaftaran/{pendaftaran}/pre-asesmen', [AsesiPersetujuanAsesmenController::class, 'show'])->name('pre-asesmen.show');
        Route::post('pendaftaran/{pendaftaran}/pre-asesmen', [AsesiPersetujuanAsesmenController::class, 'approve'])->name('pre-asesmen.approve');
        Route::get('pendaftaran/{pendaftaran}/pre-asesmen/pdf', [AsesiPersetujuanAsesmenController::class, 'pdfGabungan'])->name('pre-asesmen.pdf.gabungan');

        Route::get('pendaftaran/{pendaftaran}/banding', [AsesiBandingController::class, 'show'])->name('banding.show');
        Route::post('pendaftaran/{pendaftaran}/banding', [AsesiBandingController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('banding.store');
        Route::get('pendaftaran/{pendaftaran}/banding/pdf', [AsesiBandingController::class, 'pdf'])->name('banding.pdf');

        Route::get('pendaftaran/{pendaftaran}/umpan-balik', [AsesiUmpanBalikController::class, 'show'])->name('umpan-balik.show');
        Route::post('pendaftaran/{pendaftaran}/umpan-balik', [AsesiUmpanBalikController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('umpan-balik.store');
        Route::get('pendaftaran/{pendaftaran}/umpan-balik/pdf', [AsesiUmpanBalikController::class, 'pdf'])->name('umpan-balik.pdf');
        Route::post('pendaftaran/{pendaftaran}/dokumen', [AsesiPendaftaranController::class, 'uploadDokumen'])->name('pendaftaran.dokumen.store');
        Route::delete('pendaftaran/{pendaftaran}/dokumen/{dokumen}', [AsesiPendaftaranController::class, 'deleteDokumen'])->name('pendaftaran.dokumen.destroy');
        Route::post('pendaftaran/{pendaftaran}/ajukan', [AsesiPendaftaranController::class, 'submitDokumen'])->name('pendaftaran.submit');
        Route::get('dokumen/{dokumen}/unduh', [AsesiPendaftaranController::class, 'downloadDokumen'])->name('pendaftaran.dokumen.download');
    });
