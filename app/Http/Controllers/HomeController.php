<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function home(): View
    {
        $totalKaryawan = DB::table('karyawan')->count();
        $jadwalAktif = DB::table('setting_kunjungan')->where('status', 'Y')->count();
        $totalKegiatan = Kegiatan::query()->count();
        $kunjunganDisetujui = DB::table('kunjungan')
            ->where('status', 'disetujui')
            ->count();
        $jadwalTerbaru = DB::table('setting_kunjungan as jadwal')
            ->join('karyawan', 'karyawan.id', '=', 'jadwal.karyawan_id')
            ->where('jadwal.status', 'Y')
            ->whereDate('jadwal.tanggal', '>=', today())
            ->orderBy('jadwal.tanggal')
            ->orderBy('jadwal.jam_aktif')
            ->limit(6)
            ->get([
                'karyawan.nama',
                'jadwal.jabatan',
                'jadwal.tanggal',
                'jadwal.jam_aktif',
                'jadwal.jam_non_aktif',
            ]);
        $kegiatanTerbaru = DB::table('kegiatan')
            ->join('karyawan', 'karyawan.id', '=', 'kegiatan.karyawan_id')
            ->orderByDesc('kegiatan.tanggal_awal')
            ->limit(4)
            ->get([
                'kegiatan.jabatan',
                'kegiatan.tanggal_awal',
                'kegiatan.tanggal_akhir',
                'kegiatan.deskripsi_kegiatan',
                'karyawan.nama',
            ]);
        $pimpinan = DB::table('karyawan')
            ->orderBy('jabatan')
            ->orderBy('nama')
            ->limit(4)
            ->get(['nama', 'jabatan']);

        return view('landing.home', compact(
            'totalKaryawan',
            'jadwalAktif',
            'totalKegiatan',
            'kunjunganDisetujui',
            'jadwalTerbaru',
            'kegiatanTerbaru',
            'pimpinan'
        ));
    }
}
