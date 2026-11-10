<?php

namespace App\Http\Controllers;

use App\Models\Kunjungan;
use App\Models\SettingKunjungan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $pemohon = $request->user()->pemohon;
        abort_if($pemohon === null, 403, 'Akun ini belum terhubung dengan data pemohon.');

        $kunjungans = $pemohon->visits()
            ->with('karyawan')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->get();

        return view('visits.index', compact('kunjungans'));
    }

    public function create(Request $request): View
    {
        abort_if($request->user()->role !== User::ROLE_PEMOHON, 403);
        abort_if($request->user()->pemohon === null, 403, 'Akun ini belum terhubung dengan data pemohon.');

        $schedules = SettingKunjungan::query()
            ->with('karyawan')
            ->where('status', 'Y')
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('jam_aktif')
            ->get();

        return view('visits.create', compact('schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->role !== User::ROLE_PEMOHON, 403);
        $pemohon = $request->user()->pemohon;
        abort_if($pemohon === null, 403, 'Akun ini belum terhubung dengan data pemohon.');

        $validated = $request->validate([
            'schedule_id' => ['required', 'integer', 'exists:setting_kunjungan,id'],
            'jam' => ['required', 'date_format:H:i'],
            'keperluan' => ['required', 'string', 'max:255'],
        ]);

        $schedule = SettingKunjungan::query()
            ->where('status', 'Y')
            ->whereDate('tanggal', '>=', today())
            ->find($validated['schedule_id']);

        if ($schedule === null) {
            return back()
                ->withErrors(['schedule_id' => 'Jadwal tidak aktif atau sudah berlalu.'])
                ->withInput();
        }

        $requestedTime = $validated['jam'];
        $startTime = substr($schedule->jam_aktif, 0, 5);
        $endTime = substr($schedule->jam_non_aktif, 0, 5);

        if ($requestedTime < $startTime || $requestedTime >= $endTime) {
            return back()
                ->withErrors(['jam' => 'Waktu pengajuan harus berada dalam jam layanan yang tersedia.'])
                ->withInput();
        }

        if ($schedule->tanggal->isToday() && $requestedTime <= now()->format('H:i')) {
            return back()
                ->withErrors(['jam' => 'Waktu pengajuan harus lebih lambat dari waktu saat ini.'])
                ->withInput();
        }

        $duplicate = Kunjungan::query()
            ->where('pemohon_id', $pemohon->id)
            ->where('karyawan_id', $schedule->karyawan_id)
            ->whereDate('tanggal', $schedule->tanggal)
            ->whereTime('jam', $requestedTime)
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['jam' => 'Anda sudah memiliki pengajuan pada jadwal tersebut.'])
                ->withInput();
        }

        DB::transaction(function () use ($pemohon, $schedule, $validated, $requestedTime): void {
            Kunjungan::query()->create([
                'pemohon_id' => $pemohon->id,
                'karyawan_id' => $schedule->karyawan_id,
                'tanggal' => $schedule->tanggal,
                'jam' => $requestedTime,
                'keperluan' => $validated['keperluan'],
                'status' => Kunjungan::STATUS_MENUNGGU,
            ]);
        });

        return redirect()
            ->route('visits.index')
            ->with('status', 'Pengajuan kunjungan berhasil dikirim.');
    }

    public function destroy(Request $request, int $visit): RedirectResponse
    {
        abort_if($request->user()->role !== User::ROLE_PEMOHON, 403);
        $pemohon = $request->user()->pemohon;
        abort_if($pemohon === null, 403, 'Akun ini belum terhubung dengan data pemohon.');

        $kunjungan = $pemohon->visits()
            ->where('status', Kunjungan::STATUS_MENUNGGU)
            ->findOrFail($visit);
        $kunjungan->delete();

        return redirect()
            ->route('visits.index')
            ->with('status', 'Pengajuan yang masih menunggu berhasil dibatalkan.');
    }

    public function decide(Request $request, int $visit): RedirectResponse
    {
        abort_if($request->user()->role !== User::ROLE_KARYAWAN, 403);
        $karyawan = $request->user()->karyawan;
        abort_if($karyawan === null, 403, 'Akun ini belum terhubung dengan data karyawan.');

        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,ditolak'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $kunjungan = $karyawan->visits()
            ->where('status', Kunjungan::STATUS_MENUNGGU)
            ->findOrFail($visit);
        $kunjungan->update([
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()
            ->route('employee.workspace')
            ->with('status', 'Status pengajuan kunjungan berhasil diperbarui.');
    }
}
