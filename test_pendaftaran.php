<?php
require 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$request = \Illuminate\Http\Request::create('/');
$response = $kernel->handle($request);

// Load models
$pendaftaran = \App\Models\Pendaftaran::with('apl02')->first();

if ($pendaftaran) {
    echo $pendaftaran->id . '|' . $pendaftaran->no_pendaftaran . '|' . ($pendaftaran->apl02?->status ?? 'null');
} else {
    echo 'none';
}
