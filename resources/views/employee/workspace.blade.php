@extends('layouts.app')

@section('title', 'Ruang kerja karyawan')
@section('page-title', 'Ruang kerja karyawan')

@section('content')
    <section class="mb-4">
        <p class="eyebrow mb-2">Ruang kerja karyawan</p>
        <h1 class="section-title h2 mb-2">Halo, {{ $karyawan->nama }}</h1>
        <p class="text-secondary mb-0">Kelola jadwal dan kegiatan Anda, serta proses pengajuan yang ditujukan kepada Anda.</p>
    </section>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="surface p-3 p-md-4 mb-4">
        <h2 class="section-title h4 mb-3">Tambah jadwal layanan</h2>
        <form class="row g-3 align-items-end" method="POST" action="{{ route('employee.schedules.store') }}">
            @csrf
            <div class="col-sm-6 col-lg-3"><label class="form-label" for="tanggal">Tanggal</label><input class="form-control" id="tanggal" name="tanggal" type="date" min="{{ today()->toDateString() }}" required></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="jam_aktif">Mulai</label><input class="form-control" id="jam_aktif" name="jam_aktif" type="time" required></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="jam_non_aktif">Selesai</label><input class="form-control" id="jam_non_aktif" name="jam_non_aktif" type="time" required></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="Y">Aktif</option><option value="T">Nonaktif</option></select></div>
            <div class="col-lg-3"><button class="btn btn-teal w-100" type="submit">Tambah jadwal</button></div>
        </form>
    </section>

    <section class="surface p-3 p-md-4 mb-4">
        <h2 class="section-title h4 mb-3">Jadwal saya</h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>Jam</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->tanggal->translatedFormat('d M Y') }}</td>
                            <td>{{ substr($schedule->jam_aktif, 0, 5) }}–{{ substr($schedule->jam_non_aktif, 0, 5) }}</td>
                            <td>{{ $schedule->status === 'Y' ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="d-flex gap-2">
                                <form method="POST" action="{{ route('employee.schedules.update', $schedule) }}" class="d-flex flex-wrap gap-2">
                                    @csrf @method('PATCH')
                                    <input class="form-control form-control-sm" name="tanggal" type="date" value="{{ $schedule->tanggal->toDateString() }}" min="{{ today()->toDateString() }}" required>
                                    <input class="form-control form-control-sm" name="jam_aktif" type="time" value="{{ substr($schedule->jam_aktif, 0, 5) }}" required>
                                    <input class="form-control form-control-sm" name="jam_non_aktif" type="time" value="{{ substr($schedule->jam_non_aktif, 0, 5) }}" required>
                                    <select class="form-select form-select-sm" name="status"><option value="Y" @selected($schedule->status === 'Y')>Aktif</option><option value="T" @selected($schedule->status === 'T')>Nonaktif</option></select>
                                    <button class="btn btn-outline-primary btn-sm" type="submit">Simpan</button>
                                </form>
                                <form method="POST" action="{{ route('employee.schedules.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal ini?')">@csrf @method('DELETE')<button class="btn btn-outline-secondary btn-sm" type="submit">Hapus</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="text-center text-secondary py-4" colspan="4">Belum ada jadwal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="surface p-3 p-md-4 mb-4">
        <h2 class="section-title h4 mb-3">Pengajuan kunjungan masuk</h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>Pemohon</th><th>Keperluan</th><th>Status</th><th>Keputusan</th></tr></thead>
                <tbody>
                    @forelse ($visits as $visit)
                        <tr>
                            <td>{{ $visit->tanggal->translatedFormat('d M Y') }} · {{ substr($visit->jam, 0, 5) }}</td>
                            <td>{{ $visit->pemohon->nama }}</td>
                            <td>{{ $visit->keperluan }}</td>
                            <td>{{ ucfirst($visit->status) }}@if ($visit->catatan)<div class="small text-secondary">{{ $visit->catatan }}</div>@endif</td>
                            <td>
                                @if ($visit->status === \App\Models\Kunjungan::STATUS_MENUNGGU)
                                    <form method="POST" action="{{ route('employee.visits.decide', $visit) }}" class="d-flex flex-wrap gap-2">
                                        @csrf
                                        <input class="form-control form-control-sm" name="catatan" placeholder="Catatan (opsional)" maxlength="1000">
                                        <button class="btn btn-teal btn-sm" name="status" value="disetujui" type="submit">Setujui</button>
                                        <button class="btn btn-outline-secondary btn-sm" name="status" value="ditolak" type="submit">Tolak</button>
                                    </form>
                                @else
                                    <span class="text-secondary small">Sudah diproses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="text-center text-secondary py-4" colspan="5">Belum ada pengajuan masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="surface p-3 p-md-4">
        <h2 class="section-title h4 mb-3">Kegiatan saya</h2>
        <form class="row g-3 align-items-end mb-4" method="POST" action="{{ route('employee.activities.store') }}">
            @csrf
            <div class="col-sm-6 col-lg-3"><label class="form-label" for="tanggal_awal">Tanggal mulai</label><input class="form-control" id="tanggal_awal" name="tanggal_awal" type="date" required></div>
            <div class="col-sm-6 col-lg-3"><label class="form-label" for="tanggal_akhir">Tanggal selesai</label><input class="form-control" id="tanggal_akhir" name="tanggal_akhir" type="date" required></div>
            <div class="col-lg-4"><label class="form-label" for="deskripsi_kegiatan">Nama kegiatan</label><input class="form-control" id="deskripsi_kegiatan" name="deskripsi_kegiatan" maxlength="2000" required></div>
            <div class="col-lg-2"><button class="btn btn-teal w-100" type="submit">Tambah</button></div>
        </form>
        <div class="d-flex flex-column gap-3">
            @forelse ($activities as $activity)
                <article class="border rounded-3 p-3">
                    <form id="activity-update-{{ $activity->id }}" method="POST" action="{{ route('employee.activities.update', $activity) }}">@csrf @method('PATCH')</form>
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-6 col-lg-3"><label class="form-label small" for="start-{{ $activity->id }}">Mulai</label><input class="form-control" id="start-{{ $activity->id }}" name="tanggal_awal" type="date" value="{{ $activity->tanggal_awal->toDateString() }}" form="activity-update-{{ $activity->id }}" required></div>
                        <div class="col-sm-6 col-lg-3"><label class="form-label small" for="end-{{ $activity->id }}">Selesai</label><input class="form-control" id="end-{{ $activity->id }}" name="tanggal_akhir" type="date" value="{{ $activity->tanggal_akhir->toDateString() }}" form="activity-update-{{ $activity->id }}" required></div>
                        <div class="col-lg-4"><label class="form-label small" for="activity-{{ $activity->id }}">Kegiatan</label><input class="form-control" id="activity-{{ $activity->id }}" name="deskripsi_kegiatan" value="{{ $activity->deskripsi_kegiatan }}" maxlength="2000" form="activity-update-{{ $activity->id }}" required></div>
                        <div class="col-lg-2 d-flex gap-2"><button class="btn btn-outline-primary" form="activity-update-{{ $activity->id }}" type="submit">Simpan</button><form method="POST" action="{{ route('employee.activities.destroy', $activity) }}">@csrf @method('DELETE')<button class="btn btn-outline-secondary" type="submit">Hapus</button></form></div>
                    </div>
                </article>
            @empty
                <p class="text-secondary mb-0">Belum ada kegiatan.</p>
            @endforelse
        </div>
    </section>
@endsection
