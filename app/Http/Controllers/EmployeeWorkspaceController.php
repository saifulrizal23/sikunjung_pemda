<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Kunjungan;
use App\Models\SettingKunjungan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeWorkspaceController extends Controller
{
    public function index(Request $request): View
    {
        $karyawan = $request->user()->karyawan;
        abort_if($request->user()->role !== User::ROLE_KARYAWAN || $karyawan === null, 403);

        $schedules = $karyawan->schedules()->orderByDesc('tanggal')->get();
        $activities = $karyawan->activities()->orderByDesc('tanggal_awal')->get();
        $visits = $karyawan->visits()
            ->with('pemohon')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->get();

        return view('employee.workspace', compact('karyawan', 'schedules', 'activities', 'visits'));
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $karyawan = $this->employeeFor($request);
        $validated = $request->validate([
            'tanggal' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('setting_kunjungan')->where(fn ($query) => $query
                    ->where('karyawan_id', $karyawan->id)
                    ->whereDate('tanggal', $request->input('tanggal'))),
            ],
            'jam_aktif' => ['required', 'date_format:H:i'],
            'jam_non_aktif' => ['required', 'date_format:H:i', 'after:jam_aktif'],
            'status' => ['required', Rule::in(['Y', 'T'])],
        ]);

        $karyawan->schedules()->create([
            ...$validated,
            'jabatan' => $karyawan->jabatan,
        ]);

        return $this->workspaceStatus('Jadwal layanan berhasil ditambahkan.');
    }

    public function updateSchedule(Request $request, int $schedule): RedirectResponse
    {
        $karyawan = $this->employeeFor($request);
        $validated = $request->validate([
            'tanggal' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('setting_kunjungan')->where(fn ($query) => $query
                    ->where('karyawan_id', $karyawan->id)
                    ->whereDate('tanggal', $request->input('tanggal')))
                    ->ignore($schedule),
            ],
            'jam_aktif' => ['required', 'date_format:H:i'],
            'jam_non_aktif' => ['required', 'date_format:H:i', 'after:jam_aktif'],
            'status' => ['required', Rule::in(['Y', 'T'])],
        ]);
        $setting = $karyawan->schedules()->findOrFail($schedule);

        if (Kunjungan::query()
            ->where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', $setting->tanggal)
            ->exists()) {
            return back()->withErrors(['schedule' => 'Jadwal yang sudah memiliki pengajuan tidak dapat diubah.']);
        }

        $setting->update([
            ...$validated,
            'jabatan' => $karyawan->jabatan,
        ]);

        return $this->workspaceStatus('Jadwal layanan berhasil diperbarui.');
    }

    public function destroySchedule(Request $request, int $schedule): RedirectResponse
    {
        $setting = $this->employeeFor($request)->schedules()->findOrFail($schedule);

        if (Kunjungan::query()
            ->where('karyawan_id', $setting->karyawan_id)
            ->whereDate('tanggal', $setting->tanggal)
            ->exists()) {
            return back()->withErrors(['schedule' => 'Jadwal yang sudah memiliki pengajuan tidak dapat dihapus.']);
        }

        $setting->delete();

        return $this->workspaceStatus('Jadwal layanan berhasil dihapus.');
    }

    public function storeActivity(Request $request): RedirectResponse
    {
        $karyawan = $this->employeeFor($request);
        $validated = $request->validate([
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal'],
            'deskripsi_kegiatan' => ['required', 'string', 'max:2000'],
        ]);

        $karyawan->activities()->create([
            ...$validated,
            'jabatan' => $karyawan->jabatan,
        ]);

        return $this->workspaceStatus('Kegiatan berhasil ditambahkan.');
    }

    public function updateActivity(Request $request, int $activity): RedirectResponse
    {
        $karyawan = $this->employeeFor($request);
        $validated = $request->validate([
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal'],
            'deskripsi_kegiatan' => ['required', 'string', 'max:2000'],
        ]);

        $karyawan->activities()->findOrFail($activity)->update([
            ...$validated,
            'jabatan' => $karyawan->jabatan,
        ]);

        return $this->workspaceStatus('Kegiatan berhasil diperbarui.');
    }

    public function destroyActivity(Request $request, int $activity): RedirectResponse
    {
        $this->employeeFor($request)->activities()->findOrFail($activity)->delete();

        return $this->workspaceStatus('Kegiatan berhasil dihapus.');
    }

    private function employeeFor(Request $request): \App\Models\Karyawan
    {
        abort_if($request->user()->role !== User::ROLE_KARYAWAN, 403);

        return $request->user()->karyawan
            ?? abort(403, 'Akun ini belum terhubung dengan data karyawan.');
    }

    private function workspaceStatus(string $message): RedirectResponse
    {
        return redirect()->route('employee.workspace')->with('status', $message);
    }
}
