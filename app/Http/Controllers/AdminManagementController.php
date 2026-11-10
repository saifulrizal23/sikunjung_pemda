<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Karyawan;
use App\Models\Kunjungan;
use App\Models\Pemohon;
use App\Models\SettingKunjungan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'users' => User::query()->with(['karyawan', 'pemohon'])->orderBy('name')->get(),
            'employees' => Karyawan::query()->with('user')->orderBy('nama')->get(),
            'applicants' => Pemohon::query()->with('user')->orderBy('nama')->get(),
            'schedules' => SettingKunjungan::query()->with('karyawan')->orderByDesc('tanggal')->get(),
            'visits' => Kunjungan::query()->with(['pemohon', 'karyawan'])->orderByDesc('tanggal')->orderByDesc('jam')->get(),
            'activities' => Kegiatan::query()->with('karyawan')->orderByDesc('tanggal_awal')->get(),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->userRules($request));

        DB::transaction(function () use ($validated): void {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);

            $this->syncRoleProfile($user, $validated);
        });

        return $this->adminStatus('Akun dan profil berhasil dibuat.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate($this->userRules($request, $user));

        if ($user->role !== $validated['role'] && $this->userHasOperationalHistory($user)) {
            return back()->withErrors(['role' => 'Role akun dengan jadwal, kegiatan, atau riwayat kunjungan tidak dapat diubah.']);
        }

        if ($user->role === User::ROLE_ADMIN
            && $validated['role'] !== User::ROLE_ADMIN
            && User::query()->where('role', User::ROLE_ADMIN)->count() === 1) {
            return back()->withErrors(['role' => 'Aplikasi harus memiliki setidaknya satu akun administrator.']);
        }

        DB::transaction(function () use ($user, $validated): void {
            if ($user->role !== $validated['role']) {
                $user->karyawan()->update(['user_id' => null]);
                $user->pemohon()->update(['user_id' => null]);
            }

            $attributes = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
            ];

            if (filled($validated['password'] ?? null)) {
                $attributes['password'] = $validated['password'];
            }

            $user->update($attributes);
            $this->syncRoleProfile($user, $validated);
        });

        return $this->adminStatus('Akun dan role berhasil diperbarui.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403, 'Anda tidak dapat menghapus akun yang sedang digunakan.');

        if ($user->role === User::ROLE_ADMIN
            && User::query()->where('role', User::ROLE_ADMIN)->count() === 1) {
            return back()->withErrors(['user' => 'Aplikasi harus memiliki setidaknya satu akun administrator.']);
        }

        if ($this->userHasOperationalHistory($user)) {
            return back()->withErrors(['user' => 'Akun dengan jadwal, kegiatan, atau riwayat kunjungan tidak dapat dihapus.']);
        }

        $user->delete();

        return $this->adminStatus('Akun berhasil dihapus.');
    }

    public function storeEmployee(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:karyawan,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ]);
        Karyawan::query()->create($validated);

        return $this->adminStatus('Data karyawan berhasil ditambahkan.');
    }

    public function updateEmployee(Request $request, Karyawan $employee): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', Rule::unique('karyawan', 'nik')->ignore($employee)],
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($employee, $validated): void {
            $employee->update($validated);
            $employee->user?->update(['name' => $validated['nama']]);
            $employee->schedules()->update(['jabatan' => $validated['jabatan']]);
            $employee->activities()->update(['jabatan' => $validated['jabatan']]);
        });

        return $this->adminStatus('Data karyawan berhasil diperbarui.');
    }

    public function destroyEmployee(Karyawan $employee): RedirectResponse
    {
        if ($employee->schedules()->exists() || $employee->visits()->exists() || $employee->activities()->exists()) {
            return back()->withErrors(['employee' => 'Karyawan dengan jadwal, kegiatan, atau riwayat kunjungan tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($employee): void {
            $user = $employee->user;
            $employee->delete();
            $user?->delete();
        });

        return $this->adminStatus('Data karyawan berhasil dihapus.');
    }

    public function storeApplicant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:pemohon,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:2000'],
        ]);
        Pemohon::query()->create($validated);

        return $this->adminStatus('Data pemohon berhasil ditambahkan.');
    }

    public function updateApplicant(Request $request, Pemohon $applicant): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', Rule::unique('pemohon', 'nik')->ignore($applicant)],
            'nama' => ['required', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($applicant, $validated): void {
            $applicant->update($validated);
            $applicant->user?->update(['name' => $validated['nama']]);
        });

        return $this->adminStatus('Data pemohon berhasil diperbarui.');
    }

    public function destroyApplicant(Pemohon $applicant): RedirectResponse
    {
        if ($applicant->visits()->exists()) {
            return back()->withErrors(['applicant' => 'Pemohon dengan riwayat kunjungan tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($applicant): void {
            $user = $applicant->user;
            $applicant->delete();
            $user?->delete();
        });

        return $this->adminStatus('Data pemohon berhasil dihapus.');
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'karyawan_id' => ['required', 'exists:karyawan,id'],
            'tanggal' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('setting_kunjungan')->where(fn ($query) => $query
                    ->where('karyawan_id', $request->input('karyawan_id'))
                    ->whereDate('tanggal', $request->input('tanggal'))),
            ],
            'jam_aktif' => ['required', 'date_format:H:i'],
            'jam_non_aktif' => ['required', 'date_format:H:i', 'after:jam_aktif'],
            'status' => ['required', Rule::in(['Y', 'T'])],
        ]);
        $employee = Karyawan::query()->findOrFail($validated['karyawan_id']);
        SettingKunjungan::query()->create([
            ...$validated,
            'jabatan' => $employee->jabatan,
        ]);

        return $this->adminStatus('Jadwal layanan berhasil ditambahkan.');
    }

    public function updateSchedule(Request $request, SettingKunjungan $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'karyawan_id' => ['required', 'exists:karyawan,id'],
            'tanggal' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('setting_kunjungan')->where(fn ($query) => $query
                    ->where('karyawan_id', $request->input('karyawan_id'))
                    ->whereDate('tanggal', $request->input('tanggal')))
                    ->ignore($schedule),
            ],
            'jam_aktif' => ['required', 'date_format:H:i'],
            'jam_non_aktif' => ['required', 'date_format:H:i', 'after:jam_aktif'],
            'status' => ['required', Rule::in(['Y', 'T'])],
        ]);

        if (Kunjungan::query()->where('karyawan_id', $schedule->karyawan_id)->whereDate('tanggal', $schedule->tanggal)->exists()) {
            return back()->withErrors(['schedule' => 'Jadwal yang sudah memiliki pengajuan tidak dapat diubah.']);
        }

        $employee = Karyawan::query()->findOrFail($validated['karyawan_id']);
        $schedule->update([
            ...$validated,
            'jabatan' => $employee->jabatan,
        ]);

        return $this->adminStatus('Jadwal layanan berhasil diperbarui.');
    }

    public function destroySchedule(SettingKunjungan $schedule): RedirectResponse
    {
        if (Kunjungan::query()->where('karyawan_id', $schedule->karyawan_id)->whereDate('tanggal', $schedule->tanggal)->exists()) {
            return back()->withErrors(['schedule' => 'Jadwal yang sudah memiliki pengajuan tidak dapat dihapus.']);
        }

        $schedule->delete();

        return $this->adminStatus('Jadwal layanan berhasil dihapus.');
    }

    public function updateVisit(Request $request, Kunjungan $visit): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Kunjungan::STATUS_MENUNGGU,
                Kunjungan::STATUS_DISETUJUI,
                Kunjungan::STATUS_DITOLAK,
            ])],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);
        $visit->update($validated);

        return $this->adminStatus('Status kunjungan berhasil diperbarui.');
    }

    public function destroyVisit(Kunjungan $visit): RedirectResponse
    {
        $visit->delete();

        return $this->adminStatus('Pengajuan kunjungan berhasil dihapus.');
    }

    public function storeActivity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'karyawan_id' => ['required', 'exists:karyawan,id'],
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal'],
            'deskripsi_kegiatan' => ['required', 'string', 'max:2000'],
        ]);
        $employee = Karyawan::query()->findOrFail($validated['karyawan_id']);
        Kegiatan::query()->create([
            ...$validated,
            'jabatan' => $employee->jabatan,
        ]);

        return $this->adminStatus('Kegiatan berhasil ditambahkan.');
    }

    public function updateActivity(Request $request, Kegiatan $activity): RedirectResponse
    {
        $validated = $request->validate([
            'karyawan_id' => ['required', 'exists:karyawan,id'],
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal'],
            'deskripsi_kegiatan' => ['required', 'string', 'max:2000'],
        ]);
        $employee = Karyawan::query()->findOrFail($validated['karyawan_id']);
        $activity->update([
            ...$validated,
            'jabatan' => $employee->jabatan,
        ]);

        return $this->adminStatus('Kegiatan berhasil diperbarui.');
    }

    public function destroyActivity(Kegiatan $activity): RedirectResponse
    {
        $activity->delete();

        return $this->adminStatus('Kegiatan berhasil dihapus.');
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    private function userRules(Request $request, ?User $user = null): array
    {
        $role = $request->input('role');
        $employee = $user?->karyawan;
        $applicant = $user?->pemohon;
        $profileTable = $role === User::ROLE_KARYAWAN ? 'karyawan' : 'pemohon';
        $profileClass = $role === User::ROLE_KARYAWAN ? Karyawan::class : Pemohon::class;
        $profile = $role === User::ROLE_KARYAWAN ? $employee : ($role === User::ROLE_PEMOHON ? $applicant : null);
        $unlinkedProfile = $role === User::ROLE_KARYAWAN || $role === User::ROLE_PEMOHON
            ? $profileClass::query()->where('nik', $request->input('nik'))->whereNull('user_id')->first()
            : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_KARYAWAN, User::ROLE_PEMOHON])],
            'password' => [$user === null ? 'required' : 'nullable', 'string', 'min:8'],
            'nik' => [
                Rule::requiredIf(in_array($role, [User::ROLE_KARYAWAN, User::ROLE_PEMOHON], true)),
                'nullable',
                'digits:16',
                Rule::unique($profileTable, 'nik')->ignore($profile ?? $unlinkedProfile),
            ],
            'jabatan' => [Rule::requiredIf($role === User::ROLE_KARYAWAN), 'nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncRoleProfile(User $user, array $validated): void
    {
        if ($validated['role'] === User::ROLE_KARYAWAN) {
            $employee = $user->karyawan
                ?? Karyawan::query()->where('nik', $validated['nik'])->whereNull('user_id')->first()
                ?? new Karyawan();
            $employee->fill([
                'user_id' => $user->id,
                'nik' => $validated['nik'],
                'nama' => $validated['name'],
                'jabatan' => $validated['jabatan'],
                'no_telepon' => $validated['no_telepon'] ?? null,
            ])->save();
            $employee->schedules()->update(['jabatan' => $employee->jabatan]);
            $employee->activities()->update(['jabatan' => $employee->jabatan]);
        } elseif ($validated['role'] === User::ROLE_PEMOHON) {
            $applicant = $user->pemohon
                ?? Pemohon::query()->where('nik', $validated['nik'])->whereNull('user_id')->first()
                ?? new Pemohon();
            $applicant->fill([
                'user_id' => $user->id,
                'nik' => $validated['nik'],
                'nama' => $validated['name'],
                'no_telepon' => $validated['no_telepon'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
            ])->save();
        }
    }

    private function userHasOperationalHistory(User $user): bool
    {
        $employee = $user->karyawan;
        $applicant = $user->pemohon;

        return ($employee !== null && (
            $employee->schedules()->exists()
            || $employee->visits()->exists()
            || $employee->activities()->exists()
        )) || ($applicant !== null && $applicant->visits()->exists());
    }

    private function adminStatus(string $message): RedirectResponse
    {
        return redirect()->route('admin.index')->with('status', $message);
    }
}
