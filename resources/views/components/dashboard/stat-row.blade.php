{{--
    x-dashboard.stat-row — Pembungkus baris kartu statistik.

    Pola Tabler: <div class="row row-deck row-cards"> dengan slot berisi
    item <div class="col-sm-6 col-lg-3"> + <x-tabler.stat ...>.

    Digunakan di dashboard admin agar markup row tidak ditulis ulang di setiap view.
--}}
<div class="row row-deck row-cards">
    {{ $slot }}
</div>
