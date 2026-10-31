<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

final class AdminProvinsiController extends Controller
{
    public function index(): Response
    {
        abort_unless(false, 501, 'Belum diimplementasi.');
    }
}
