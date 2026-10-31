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
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\JadwalController as AdminJadwalController;
use App\Http\Controllers\Admin\NotifikasiController as AdminNotifikasiController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\PendaftaranController as AdminPendaftaranController;
use App\Http\Controllers\Admin\RekeningBankController as AdminRekeningBankController;
use App\Http\Controllers\Admin\SertifikatController as AdminSertifikatController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Asesi\ActivationController as AsesiActivationController;
use App\Http\Controllers\Asesi\Apl02Controller as AsesiApl02Controller;
use App\Http\Controllers\Asesi\PanduanController as AsesiPanduanController;
use App\Http\Controllers\Asesi\PembayaranController as AsesiPembayaranController;
use App\Http\Controllers\Asesi\PendaftaranController as AsesiPendaftaranController;
use App\Http\Controllers\Asesi\ProfilController as AsesiProfilController;
use App\Http\Controllers\Asesi\SertifikatController as AsesiSertifikatController;
use App\Http\Controllers\Asesor\Apl02Controller as AsesorApl02Controller;
use App\Http\Controllers\Asesor\HasilAsesmenController as AsesorHasilAsesmenController;
use App\Http\Controllers\Asesor\JadwalController as AsesorJadwalController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SertifikatVerifikasiController;
use App\Http\Controllers\WidgetController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/beranda', [PublicController::class, 'index'])->name('beranda');

// Verifikasi keaslian sertifikat (publik — tidak perlu auth) — FR-CMS-06
Route::get('/sertifikat/{token}', [SertifikatVerifikasiController::class, 'verify'])
    ->where('token', '[a-zA-Z0-9]{32}')
    ->name('sertifikat.verifikasi');

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
});

Route::middleware(['auth', 'tenant.context'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/widgets/{key}', [WidgetController::class, 'show'])->name('dashboard.widget');
    Route::post('/tenant/switch', [DashboardController::class, 'switchInstitution'])
        ->name('tenant.switch');

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
        Route::resource('institution', AdminInstitutionController::class)->except(['show']);
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
        Route::post('pendaftaran/{pendaftaran}/setujui', [AdminPendaftaranController::class, 'approve'])->name('pendaftaran.approve');
        Route::post('pendaftaran/{pendaftaran}/tolak', [AdminPendaftaranController::class, 'reject'])->name('pendaftaran.reject');

        Route::get('pelaporan', [AdminPelaporanController::class, 'index'])->name('pelaporan.index');
        Route::get('pelaporan/apl02', [AdminPelaporanController::class, 'apl02'])->name('pelaporan.apl02');
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
    });

Route::prefix('asesor')
    ->name('asesor.')
    ->middleware(['auth', 'role:asesor'])
    ->group(function (): void {
        Route::get('jadwal', [AsesorJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('jadwal/{jadwal}/peserta', [AsesorJadwalController::class, 'peserta'])->name('jadwal.peserta');

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
        Route::get('pendaftaran/{pendaftaran}/mak02/pdf', [AsesorHasilAsesmenController::class, 'pdf'])->name('mak02.pdf');
    });

Route::prefix('asesi')
    ->name('asesi.')
    ->middleware(['auth', 'role:asesi'])
    ->group(function (): void {
        Route::get('profil', [AsesiProfilController::class, 'edit'])->name('profil.edit');
        Route::put('profil', [AsesiProfilController::class, 'update'])->name('profil.update');
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
        Route::post('pendaftaran/{pendaftaran}/dokumen', [AsesiPendaftaranController::class, 'uploadDokumen'])->name('pendaftaran.dokumen.store');
        Route::delete('pendaftaran/{pendaftaran}/dokumen/{dokumen}', [AsesiPendaftaranController::class, 'deleteDokumen'])->name('pendaftaran.dokumen.destroy');
        Route::post('pendaftaran/{pendaftaran}/ajukan', [AsesiPendaftaranController::class, 'submitDokumen'])->name('pendaftaran.submit');
        Route::get('dokumen/{dokumen}/unduh', [AsesiPendaftaranController::class, 'downloadDokumen'])->name('pendaftaran.dokumen.download');
    });
