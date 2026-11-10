<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $roleLabel = match ($user->role) {
            User::ROLE_ADMIN => 'Administrator',
            User::ROLE_KARYAWAN => 'Karyawan',
            User::ROLE_PEMOHON => 'Pemohon',
            default => abort(403),
        };

        $stats = [];
        $recentVisits = collect();

        if ($user->role === User::ROLE_ADMIN) {
            $stats = [
                'Total karyawan' => DB::table('karyawan')->count(),
                'Total pemohon' => DB::table('pemohon')->count(),
                'Menunggu diproses' => DB::table('kunjungan')->where('status', 'menunggu')->count(),
                'Kunjungan disetujui' => DB::table('kunjungan')->where('status', 'disetujui')->count(),
            ];
            $recentVisits = $this->recentVisits();
        } elseif ($user->role === User::ROLE_KARYAWAN) {
            $karyawanId = DB::table('karyawan')->where('user_id', $user->id)->value('id');
            $stats = [
                'Kunjungan masuk' => DB::table('kunjungan')->where('karyawan_id', $karyawanId ?? 0)->count(),
                'Menunggu diproses' => DB::table('kunjungan')
                    ->where('karyawan_id', $karyawanId ?? 0)
                    ->where('status', 'menunggu')
                    ->count(),
                'Kegiatan saya' => DB::table('kegiatan')->where('karyawan_id', $karyawanId ?? 0)->count(),
            ];
            $recentVisits = $karyawanId === null ? collect() : $this->recentVisits($karyawanId);
        } else {
            $pemohonId = DB::table('pemohon')->where('user_id', $user->id)->value('id');
            $stats = [
                'Pengajuan kunjungan' => DB::table('kunjungan')->where('pemohon_id', $pemohonId ?? 0)->count(),
                'Menunggu konfirmasi' => DB::table('kunjungan')
                    ->where('pemohon_id', $pemohonId ?? 0)
                    ->where('status', 'menunggu')
                    ->count(),
                'Disetujui' => DB::table('kunjungan')
                    ->where('pemohon_id', $pemohonId ?? 0)
                    ->where('status', 'disetujui')
                    ->count(),
            ];
            $recentVisits = $pemohonId === null ? collect() : $this->recentVisits(null, $pemohonId);
        }

        return view('dashboard.index', compact('user', 'roleLabel', 'stats', 'recentVisits'));
    }

    private function recentVisits(?int $karyawanId = null, ?int $pemohonId = null): Collection
    {
        return DB::table('kunjungan as kunjungan')
            ->leftJoin('pemohon', 'pemohon.id', '=', 'kunjungan.pemohon_id')
            ->leftJoin('karyawan', 'karyawan.id', '=', 'kunjungan.karyawan_id')
            ->when($karyawanId !== null, fn ($query) => $query->where('kunjungan.karyawan_id', $karyawanId))
            ->when($pemohonId !== null, fn ($query) => $query->where('kunjungan.pemohon_id', $pemohonId))
            ->orderByDesc('kunjungan.tanggal')
            ->orderByDesc('kunjungan.jam')
            ->limit(6)
            ->get([
                'kunjungan.tanggal',
                'kunjungan.jam',
                'kunjungan.keperluan',
                'kunjungan.status',
                'pemohon.nama as nama_pemohon',
                'karyawan.nama as nama_karyawan',
            ]);
    }
}
