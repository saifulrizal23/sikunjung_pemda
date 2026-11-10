<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    public function jadwal(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:Y,T'],
        ]);

        $queryJadwal = DB::table('setting_kunjungan as jadwal')
            ->join('karyawan', 'karyawan.id', '=', 'jadwal.karyawan_id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $queryJadwal->where(function ($query) use ($search): void {
                $query->where('karyawan.nama', 'like', "%{$search}%")
                    ->orWhere('karyawan.nik', 'like', "%{$search}%")
                    ->orWhere('jadwal.jabatan', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $queryJadwal->where('jadwal.status', $filters['status']);
        } else {
            $queryJadwal->where('jadwal.status', 'Y');
        }

        $settingKunjungans = $queryJadwal
            ->orderBy('jadwal.tanggal')
            ->orderBy('jadwal.jam_aktif')
            ->get([
                'jadwal.id',
                'jadwal.jabatan',
                'jadwal.tanggal',
                'jadwal.jam_aktif',
                'jadwal.jam_non_aktif',
                'jadwal.status',
                'karyawan.nama',
            ]);
        $kegiatans = DB::table('kegiatan')
            ->join('karyawan', 'karyawan.id', '=', 'kegiatan.karyawan_id')
            ->orderBy('kegiatan.tanggal_awal')
            ->get([
                'kegiatan.jabatan',
                'kegiatan.tanggal_awal',
                'kegiatan.tanggal_akhir',
                'kegiatan.deskripsi_kegiatan',
                'karyawan.nama',
            ]);

        return view('jadwal', compact('settingKunjungans', 'kegiatans'));
    }
}
