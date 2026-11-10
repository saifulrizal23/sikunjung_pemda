@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <section class="hero-panel p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <p class="text-info text-uppercase fw-semibold small mb-2">Dashboard {{ $roleLabel }}</p>
                <h1 class="display-6 fw-bold mb-2">Halo, {{ $user->name }}</h1>
                <p class="text-white-50 mb-0">Ringkasan aktivitas layanan kunjungan Anda.</p>
            </div>
            <span class="badge rounded-pill bg-white text-dark px-3 py-2 align-self-start align-self-md-auto">{{ $roleLabel }}</span>
        </div>
    </section>

    <section class="row g-3 mb-4" aria-label="Ringkasan dashboard">
        @foreach ($stats as $label => $value)
            <div class="col-sm-6 col-xl-3">
                <article class="surface p-4 h-100">
                    <div class="eyebrow mb-3">{{ $label }}</div>
                    <div class="stat-value">{{ $value }}</div>
                </article>
            </div>
        @endforeach
    </section>

    <section class="d-flex flex-wrap gap-2 mb-4" aria-label="Aksi cepat">
        @if ($user->role === \App\Models\User::ROLE_ADMIN)
            <a class="btn btn-teal" href="{{ route('admin.index') }}">Buka manajemen sistem</a>
        @elseif ($user->role === \App\Models\User::ROLE_KARYAWAN)
            <a class="btn btn-teal" href="{{ route('employee.workspace') }}">Buka ruang kerja</a>
        @elseif ($user->role === \App\Models\User::ROLE_PEMOHON)
            <a class="btn btn-teal" href="{{ route('visits.create') }}">Ajukan kunjungan</a>
            <a class="btn btn-outline-secondary" href="{{ route('visits.index') }}">Lihat pengajuan saya</a>
        @endif
    </section>

    <section class="surface p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <p class="eyebrow mb-1">Aktivitas</p>
                <h2 class="section-title h4 mb-0">Kunjungan terbaru</h2>
            </div>
            <a class="btn btn-outline-success btn-sm" href="{{ route('jadwal') }}">Jadwal publik</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pemohon</th>
                        <th>Tujuan</th>
                        <th>Keperluan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentVisits as $visit)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($visit->tanggal)->translatedFormat('d M Y') }}<br><span class="small text-secondary">{{ substr($visit->jam, 0, 5) }}</span></td>
                            <td>{{ $visit->nama_pemohon ?? '—' }}</td>
                            <td>{{ $visit->nama_karyawan ?? '—' }}</td>
                            <td>{{ $visit->keperluan }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $visit->status === 'disetujui' ? 'badge-soft' : ($visit->status === 'ditolak' ? 'text-bg-danger' : 'text-bg-warning') }}">
                                    {{ ucfirst($visit->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="text-center text-secondary py-5" colspan="5">Belum ada pengajuan kunjungan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
