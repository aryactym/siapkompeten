<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PanduanController extends Controller
{
    public function __invoke(): View
    {
        return view('asesi.panduan');
    }
}
