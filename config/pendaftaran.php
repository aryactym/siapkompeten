<?php

return [
    /*
     * Urutan bayar vs plotting dapat disesuaikan per LSP (System Design §16 no. 2).
     * Default true sejak Fase 4: pendaftaran dengan tagihan > 0 wajib bayar
     * sebelum masuk antrean plotting jadwal.
     */
    'payment_before_scheduling' => (bool) env('PENDAFTARAN_PAYMENT_BEFORE_SCHEDULING', true),

    'document_max_size_kb' => 5120,

    'document_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
];
