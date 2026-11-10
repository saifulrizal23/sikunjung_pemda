<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class StrukturController extends Controller
{
    public function struktur(): View
    {
        $karyawans = DB::table('karyawan')
            ->orderBy('jabatan')
            ->orderBy('nama')
            ->get(['nama', 'jabatan', 'no_telepon']);

        return view('landing.struktur', compact('karyawans'));
    }
}
