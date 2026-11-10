@extends('layouts.app')

@section('title', 'Manajemen sistem')
@section('page-title', 'Manajemen sistem')

@section('content')
    <section class="mb-4">
        <p class="eyebrow mb-2">Administrasi</p>
        <h1 class="section-title h2 mb-2">Manajemen sistem</h1>
        <p class="text-secondary mb-0">Kelola akun, data layanan, dan seluruh pengajuan kunjungan.</p>
    </section>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <nav class="surface d-flex flex-wrap gap-2 p-3 mb-4" aria-label="Bagian manajemen">
        <a class="btn btn-outline-secondary btn-sm" href="#accounts">Akun dan role</a>
        <a class="btn btn-outline-secondary btn-sm" href="#employees">Karyawan</a>
        <a class="btn btn-outline-secondary btn-sm" href="#applicants">Pemohon</a>
        <a class="btn btn-outline-secondary btn-sm" href="#schedules">Jadwal</a>
        <a class="btn btn-outline-secondary btn-sm" href="#visits">Kunjungan</a>
        <a class="btn btn-outline-secondary btn-sm" href="#activities">Kegiatan</a>
    </nav>

    <section class="surface p-3 p-md-4 mb-4" id="accounts">
        <h2 class="section-title h4 mb-3">Buat akun dan tetapkan role</h2>
        <form class="row g-3" method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="col-md-4"><label class="form-label" for="new-user-name">Nama</label><input class="form-control" id="new-user-name" name="name" value="{{ old('name') }}" required></div>
            <div class="col-md-4"><label class="form-label" for="new-user-email">Email</label><input class="form-control" id="new-user-email" name="email" type="email" value="{{ old('email') }}" required></div>
            <div class="col-md-4"><label class="form-label" for="new-user-password">Kata sandi awal</label><input class="form-control" id="new-user-password" name="password" type="password" minlength="8" required></div>
            <div class="col-md-4"><label class="form-label" for="new-user-role">Role</label><select class="form-select" id="new-user-role" name="role" required><option value="admin">Admin</option><option value="karyawan">Karyawan</option><option value="pemohon" selected>Pemohon</option></select></div>
            <div class="col-md-4"><label class="form-label" for="new-user-nik">NIK (wajib untuk karyawan/pemohon)</label><input class="form-control" id="new-user-nik" name="nik" inputmode="numeric" maxlength="16"></div>
            <div class="col-md-4"><label class="form-label" for="new-user-jabatan">Jabatan (wajib untuk karyawan)</label><input class="form-control" id="new-user-jabatan" name="jabatan"></div>
            <div class="col-md-4"><label class="form-label" for="new-user-phone">Nomor telepon</label><input class="form-control" id="new-user-phone" name="no_telepon"></div>
            <div class="col-md-8"><label class="form-label" for="new-user-address">Alamat (untuk pemohon)</label><input class="form-control" id="new-user-address" name="alamat"></div>
            <div class="col-12"><button class="btn btn-teal" type="submit">Buat akun</button></div>
        </form>

        <hr class="my-4">
        <h3 class="section-title h5 mb-3">Akun terdaftar</h3>
        <div class="d-flex flex-column gap-2">
            @foreach ($users as $account)
                @php
                    $profile = $account->karyawan ?? $account->pemohon;
                @endphp
                <details class="border rounded-3 p-3">
                    <summary class="d-flex flex-wrap align-items-center gap-2">
                        <strong>{{ $account->name }}</strong>
                        <span class="text-secondary">{{ $account->email }}</span>
                        <span class="badge text-bg-light">{{ ucfirst($account->role) }}</span>
                    </summary>
                    <form class="row g-2 mt-2" method="POST" action="{{ route('admin.users.update', $account) }}">
                        @csrf @method('PATCH')
                        <div class="col-md-4"><label class="form-label small" for="user-name-{{ $account->id }}">Nama</label><input class="form-control" id="user-name-{{ $account->id }}" name="name" value="{{ $account->name }}" required></div>
                        <div class="col-md-4"><label class="form-label small" for="user-email-{{ $account->id }}">Email</label><input class="form-control" id="user-email-{{ $account->id }}" name="email" type="email" value="{{ $account->email }}" required></div>
                        <div class="col-md-4"><label class="form-label small" for="user-role-{{ $account->id }}">Role</label><select class="form-select" id="user-role-{{ $account->id }}" name="role"><option value="admin" @selected($account->role === 'admin')>Admin</option><option value="karyawan" @selected($account->role === 'karyawan')>Karyawan</option><option value="pemohon" @selected($account->role === 'pemohon')>Pemohon</option></select></div>
                        <div class="col-md-4"><label class="form-label small" for="user-nik-{{ $account->id }}">NIK (untuk karyawan/pemohon)</label><input class="form-control" id="user-nik-{{ $account->id }}" name="nik" value="{{ $profile?->nik }}" maxlength="16"></div>
                        <div class="col-md-4"><label class="form-label small" for="user-jabatan-{{ $account->id }}">Jabatan (untuk karyawan)</label><input class="form-control" id="user-jabatan-{{ $account->id }}" name="jabatan" value="{{ $account->karyawan?->jabatan }}"></div>
                        <div class="col-md-4"><label class="form-label small" for="user-phone-{{ $account->id }}">Nomor telepon</label><input class="form-control" id="user-phone-{{ $account->id }}" name="no_telepon" value="{{ $profile?->no_telepon }}"></div>
                        <div class="col-md-8"><label class="form-label small" for="user-address-{{ $account->id }}">Alamat (pemohon)</label><input class="form-control" id="user-address-{{ $account->id }}" name="alamat" value="{{ $account->pemohon?->alamat }}"></div>
                        <div class="col-md-4"><label class="form-label small" for="user-password-{{ $account->id }}">Kata sandi baru (opsional)</label><input class="form-control" id="user-password-{{ $account->id }}" name="password" type="password" minlength="8"></div>
                        <div class="col-12"><button class="btn btn-outline-primary btn-sm" type="submit">Simpan akun</button></div>
                    </form>
                    <form class="mt-2" method="POST" action="{{ route('admin.users.destroy', $account) }}" onsubmit="return confirm('Hapus akun ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit" @disabled(auth()->user()->is($account))>Hapus akun</button></form>
                </details>
            @endforeach
        </div>
    </section>

    <section class="surface p-3 p-md-4 mb-4" id="employees">
        <h2 class="section-title h4 mb-3">Data karyawan</h2>
        <form class="row g-2 align-items-end mb-4" method="POST" action="{{ route('admin.employees.store') }}">
            @csrf
            <div class="col-md-3"><label class="form-label" for="employee-nik">NIK</label><input class="form-control" id="employee-nik" name="nik" maxlength="16" required></div>
            <div class="col-md-3"><label class="form-label" for="employee-name">Nama</label><input class="form-control" id="employee-name" name="nama" required></div>
            <div class="col-md-3"><label class="form-label" for="employee-position">Jabatan</label><input class="form-control" id="employee-position" name="jabatan" required></div>
            <div class="col-md-2"><label class="form-label" for="employee-phone">Telepon</label><input class="form-control" id="employee-phone" name="no_telepon"></div>
            <div class="col-md-1"><button class="btn btn-teal w-100" type="submit">Tambah</button></div>
        </form>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nama / akun</th><th>NIK</th><th>Jabatan</th><th>Telepon</th><th>Aksi</th></tr></thead><tbody>
            @forelse ($employees as $employee)
                <tr>
                    <td><form id="employee-update-{{ $employee->id }}" method="POST" action="{{ route('admin.employees.update', $employee) }}">@csrf @method('PATCH')</form><input class="form-control form-control-sm mb-1" form="employee-update-{{ $employee->id }}" name="nama" value="{{ $employee->nama }}" required><span class="small text-secondary">{{ $employee->user?->email ?? 'Belum memiliki akun login' }}</span></td>
                    <td><input class="form-control form-control-sm" form="employee-update-{{ $employee->id }}" name="nik" value="{{ $employee->nik }}" maxlength="16" required></td>
                    <td><input class="form-control form-control-sm" form="employee-update-{{ $employee->id }}" name="jabatan" value="{{ $employee->jabatan }}" required></td>
                    <td><input class="form-control form-control-sm" form="employee-update-{{ $employee->id }}" name="no_telepon" value="{{ $employee->no_telepon }}"></td>
                    <td class="d-flex gap-2"><button class="btn btn-outline-primary btn-sm" form="employee-update-{{ $employee->id }}" type="submit">Simpan</button><form method="POST" action="{{ route('admin.employees.destroy', $employee) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form></td>
                </tr>
            @empty
                <tr><td class="text-secondary" colspan="5">Belum ada data karyawan.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>

    <section class="surface p-3 p-md-4 mb-4" id="applicants">
        <h2 class="section-title h4 mb-3">Data pemohon</h2>
        <form class="row g-2 align-items-end mb-4" method="POST" action="{{ route('admin.applicants.store') }}">
            @csrf
            <div class="col-md-3"><label class="form-label" for="applicant-nik">NIK</label><input class="form-control" id="applicant-nik" name="nik" maxlength="16" required></div>
            <div class="col-md-3"><label class="form-label" for="applicant-name">Nama</label><input class="form-control" id="applicant-name" name="nama" required></div>
            <div class="col-md-2"><label class="form-label" for="applicant-phone">Telepon</label><input class="form-control" id="applicant-phone" name="no_telepon"></div>
            <div class="col-md-3"><label class="form-label" for="applicant-address">Alamat</label><input class="form-control" id="applicant-address" name="alamat"></div>
            <div class="col-md-1"><button class="btn btn-teal w-100" type="submit">Tambah</button></div>
        </form>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nama / akun</th><th>NIK</th><th>Telepon</th><th>Alamat</th><th>Aksi</th></tr></thead><tbody>
            @forelse ($applicants as $applicant)
                <tr>
                    <td><form id="applicant-update-{{ $applicant->id }}" method="POST" action="{{ route('admin.applicants.update', $applicant) }}">@csrf @method('PATCH')</form><input class="form-control form-control-sm mb-1" form="applicant-update-{{ $applicant->id }}" name="nama" value="{{ $applicant->nama }}" required><span class="small text-secondary">{{ $applicant->user?->email ?? 'Belum memiliki akun login' }}</span></td>
                    <td><input class="form-control form-control-sm" form="applicant-update-{{ $applicant->id }}" name="nik" value="{{ $applicant->nik }}" maxlength="16" required></td>
                    <td><input class="form-control form-control-sm" form="applicant-update-{{ $applicant->id }}" name="no_telepon" value="{{ $applicant->no_telepon }}"></td>
                    <td><input class="form-control form-control-sm" form="applicant-update-{{ $applicant->id }}" name="alamat" value="{{ $applicant->alamat }}"></td>
                    <td class="d-flex gap-2"><button class="btn btn-outline-primary btn-sm" form="applicant-update-{{ $applicant->id }}" type="submit">Simpan</button><form method="POST" action="{{ route('admin.applicants.destroy', $applicant) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form></td>
                </tr>
            @empty
                <tr><td class="text-secondary" colspan="5">Belum ada data pemohon.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>

    <section class="surface p-3 p-md-4 mb-4" id="schedules">
        <h2 class="section-title h4 mb-3">Jadwal layanan</h2>
        <form class="row g-2 align-items-end mb-4" method="POST" action="{{ route('admin.schedules.store') }}">
            @csrf
            <div class="col-md-3"><label class="form-label" for="schedule-employee">Karyawan</label><select class="form-select" id="schedule-employee" name="karyawan_id" required><option value="">Pilih karyawan</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->nama }} · {{ $employee->jabatan }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label" for="schedule-date">Tanggal</label><input class="form-control" id="schedule-date" name="tanggal" type="date" min="{{ today()->toDateString() }}" required></div>
            <div class="col-md-2"><label class="form-label" for="schedule-start">Mulai</label><input class="form-control" id="schedule-start" name="jam_aktif" type="time" required></div>
            <div class="col-md-2"><label class="form-label" for="schedule-end">Selesai</label><input class="form-control" id="schedule-end" name="jam_non_aktif" type="time" required></div>
            <div class="col-md-2"><label class="form-label" for="schedule-status">Status</label><select class="form-select" id="schedule-status" name="status"><option value="Y">Aktif</option><option value="T">Nonaktif</option></select></div>
            <div class="col-md-1"><button class="btn btn-teal w-100" type="submit">Tambah</button></div>
        </form>
        <div class="d-flex flex-column gap-2">
            @forelse ($schedules as $schedule)
                <article class="border rounded-3 p-3">
                    <div class="small text-secondary mb-2">{{ $schedule->karyawan->nama }} · {{ $schedule->jabatan }}</div>
                    <div class="d-flex flex-wrap align-items-end gap-2">
                        <form class="d-flex flex-wrap align-items-end gap-2" method="POST" action="{{ route('admin.schedules.update', $schedule) }}">
                            @csrf @method('PATCH')
                            <div><label class="form-label small" for="schedule-employee-{{ $schedule->id }}">Karyawan</label><select class="form-select form-select-sm" id="schedule-employee-{{ $schedule->id }}" name="karyawan_id">@foreach ($employees as $employee)<option value="{{ $employee->id }}" @selected($schedule->karyawan_id === $employee->id)>{{ $employee->nama }}</option>@endforeach</select></div>
                            <div><label class="form-label small" for="schedule-date-{{ $schedule->id }}">Tanggal</label><input class="form-control form-control-sm" id="schedule-date-{{ $schedule->id }}" name="tanggal" type="date" value="{{ $schedule->tanggal->toDateString() }}" min="{{ today()->toDateString() }}" required></div>
                            <div><label class="form-label small" for="schedule-start-{{ $schedule->id }}">Mulai</label><input class="form-control form-control-sm" id="schedule-start-{{ $schedule->id }}" name="jam_aktif" type="time" value="{{ substr($schedule->jam_aktif, 0, 5) }}" required></div>
                            <div><label class="form-label small" for="schedule-end-{{ $schedule->id }}">Selesai</label><input class="form-control form-control-sm" id="schedule-end-{{ $schedule->id }}" name="jam_non_aktif" type="time" value="{{ substr($schedule->jam_non_aktif, 0, 5) }}" required></div>
                            <div><label class="form-label small" for="schedule-status-{{ $schedule->id }}">Status</label><select class="form-select form-select-sm" id="schedule-status-{{ $schedule->id }}" name="status"><option value="Y" @selected($schedule->status === 'Y')>Aktif</option><option value="T" @selected($schedule->status === 'T')>Nonaktif</option></select></div>
                            <button class="btn btn-outline-primary btn-sm" type="submit">Simpan</button>
                        </form>
                        <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form>
                    </div>
                </article>
            @empty
                <p class="text-secondary mb-0">Belum ada jadwal layanan.</p>
            @endforelse
        </div>
    </section>

    <section class="surface p-3 p-md-4 mb-4" id="visits">
        <h2 class="section-title h4 mb-3">Semua pengajuan kunjungan</h2>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Tanggal</th><th>Pemohon</th><th>Karyawan</th><th>Keperluan</th><th>Status dan catatan</th><th>Aksi</th></tr></thead><tbody>
            @forelse ($visits as $visit)
                <tr><td>{{ $visit->tanggal->translatedFormat('d M Y') }} · {{ substr($visit->jam, 0, 5) }}</td><td>{{ $visit->pemohon->nama }}</td><td>{{ $visit->karyawan->nama }}</td><td>{{ $visit->keperluan }}</td>
                    <td><form method="POST" action="{{ route('admin.visits.update', $visit) }}" class="d-flex flex-wrap gap-2">@csrf @method('PATCH')
                        <select class="form-select form-select-sm" name="status"><option value="menunggu" @selected($visit->status === 'menunggu')>Menunggu</option><option value="disetujui" @selected($visit->status === 'disetujui')>Disetujui</option><option value="ditolak" @selected($visit->status === 'ditolak')>Ditolak</option></select>
                        <input class="form-control form-control-sm" name="catatan" value="{{ $visit->catatan }}" placeholder="Catatan" maxlength="1000">
                        <button class="btn btn-outline-primary btn-sm" type="submit">Simpan</button>
                    </form></td>
                    <td><form method="POST" action="{{ route('admin.visits.destroy', $visit) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form></td>
                </tr>
            @empty
                <tr><td class="text-secondary" colspan="6">Belum ada pengajuan kunjungan.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>

    <section class="surface p-3 p-md-4" id="activities">
        <h2 class="section-title h4 mb-3">Kegiatan instansi</h2>
        <form class="row g-2 align-items-end mb-4" method="POST" action="{{ route('admin.activities.store') }}">
            @csrf
            <div class="col-md-3"><label class="form-label" for="activity-employee">Karyawan</label><select class="form-select" id="activity-employee" name="karyawan_id" required><option value="">Pilih karyawan</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->nama }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label" for="activity-start">Tanggal mulai</label><input class="form-control" id="activity-start" name="tanggal_awal" type="date" required></div>
            <div class="col-md-2"><label class="form-label" for="activity-end">Tanggal selesai</label><input class="form-control" id="activity-end" name="tanggal_akhir" type="date" required></div>
            <div class="col-md-4"><label class="form-label" for="activity-description">Nama kegiatan</label><input class="form-control" id="activity-description" name="deskripsi_kegiatan" maxlength="2000" required></div>
            <div class="col-md-1"><button class="btn btn-teal w-100" type="submit">Tambah</button></div>
        </form>
        <div class="d-flex flex-column gap-2">
            @forelse ($activities as $activity)
                <article class="border rounded-3 p-3">
                    <form id="activity-update-{{ $activity->id }}" method="POST" action="{{ route('admin.activities.update', $activity) }}">@csrf @method('PATCH')</form>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3"><label class="form-label small" for="activity-employee-{{ $activity->id }}">Karyawan</label><select class="form-select" id="activity-employee-{{ $activity->id }}" name="karyawan_id" form="activity-update-{{ $activity->id }}">@foreach ($employees as $employee)<option value="{{ $employee->id }}" @selected($activity->karyawan_id === $employee->id)>{{ $employee->nama }}</option>@endforeach</select></div>
                        <div class="col-md-2"><label class="form-label small" for="activity-start-{{ $activity->id }}">Mulai</label><input class="form-control" id="activity-start-{{ $activity->id }}" name="tanggal_awal" type="date" value="{{ $activity->tanggal_awal->toDateString() }}" form="activity-update-{{ $activity->id }}" required></div>
                        <div class="col-md-2"><label class="form-label small" for="activity-end-{{ $activity->id }}">Selesai</label><input class="form-control" id="activity-end-{{ $activity->id }}" name="tanggal_akhir" type="date" value="{{ $activity->tanggal_akhir->toDateString() }}" form="activity-update-{{ $activity->id }}" required></div>
                        <div class="col-md-3"><label class="form-label small" for="activity-description-{{ $activity->id }}">Kegiatan</label><input class="form-control" id="activity-description-{{ $activity->id }}" name="deskripsi_kegiatan" value="{{ $activity->deskripsi_kegiatan }}" maxlength="2000" form="activity-update-{{ $activity->id }}" required></div>
                        <div class="col-md-2 d-flex gap-2"><button class="btn btn-outline-primary" form="activity-update-{{ $activity->id }}" type="submit">Simpan</button><form method="POST" action="{{ route('admin.activities.destroy', $activity) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">Hapus</button></form></div>
                    </div>
                </article>
            @empty
                <p class="text-secondary mb-0">Belum ada kegiatan.</p>
            @endforelse
        </div>
    </section>
@endsection
