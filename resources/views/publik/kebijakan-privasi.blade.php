@extends('layouts.public')

@section('title', 'Kebijakan Privasi')
@section('meta_description', 'Kebijakan privasi data pribadi asesi pada Sistem Informasi Lembaga Sertifikasi Profesi SiapKompeten.')

@section('content')
<div class="page-body">
    <div class="container container-md py-4">
        <div class="card">
            <div class="card-body">
                <h1 class="h1 mb-3">Kebijakan Privasi</h1>
                <p class="text-secondary">Berlaku untuk seluruh data pribadi yang dihimpun melalui formulir registrasi dan proses sertifikasi pada sistem ini.</p>

                <h2 class="h3 mt-4">1. Data yang Kami Himpun</h2>
                <ul>
                    <li>Data identitas: nama lengkap, NIK (dienkripsi dan digunakan untuk login), jenis kelamin, tempat dan tanggal lahir.</li>
                    <li>Data kontak: alamat rumah, nomor telepon/HP, email, serta kota dan provinsi.</li>
                    <li>Data pendidikan dan pekerjaan: pendidikan terakhir, instansi, jabatan, dan alamat kantor.</li>
                    <li>Data dokumen persyaratan dan hasil asesmen yang Anda unggah atau isi selama proses sertifikasi.</li>
                </ul>

                <h2 class="h3 mt-4">2. Tujuan Pemrosesan</h2>
                <ul>
                    <li>Memverifikasi identitas dan kelengkapan persyaratan asesmen.</li>
                    <li>Menjadwalkan, melaksanakan, dan merekam hasil asesmen kompetensi.</li>
                    <li>Menerbitkan dan memverifikasi sertifikat kompetensi.</li>
                    <li>Memenuhi kewajiban pelaporan lembaga sertifikasi sesuai ketentuan BNSP.</li>
                </ul>

                <h2 class="h3 mt-4">3. Kerahasiaan dan Keamanan</h2>
                <p>NIK disimpan dalam bentuk terenkripsi dan dipakai untuk keperluan login. Dokumen persyaratan disimpan pada penyimpanan privat dan hanya dapat diakses oleh petugas yang berwenang. Kami menerapkan kontrol akses, pencatatan aktivitas, dan standar keamanan OWASP Top 10:2025.</p>

                <h2 class="h3 mt-4">4. Berbagi Data</h2>
                <p>Data Anda hanya dibagikan kepada pihak yang terlibat dalam proses sertifikasi (asesor, komite/validasi, dan lembaga sertifikasi/BNSP sesuai ketentuan) atau apabila diwajibkan oleh peraturan perundang-undangan. Kami tidak menjual data pribadi Anda.</p>

                <h2 class="h3 mt-4">5. Hak Anda</h2>
                <p>Anda dapat meminta akses, koreksi, atau penghapusan data pribadi Anda sesuai ketentuan yang berlaku, dengan menghubungi lembaga melalui kontak resmi yang tercantum pada halaman beranda.</p>

                <h2 class="h3 mt-4">6. Persetujuan</h2>
                <p>Dengan mencentang persetujuan pada formulir registrasi, Anda menyetujui pemrosesan data pribadi sebagaimana dijelaskan di atas.</p>

                <a href="{{ route('register') }}" class="btn btn-primary mt-3">Kembali ke Form Registrasi</a>
            </div>
        </div>
    </div>
</div>
@endsection
