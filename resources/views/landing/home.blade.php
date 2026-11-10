@extends('layouts.public')

@section('title', 'Beranda')
@section('page-title', 'Beranda')

@section('content')
    <section class="hero-panel p-4 p-md-5 mb-4 mb-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <p class="text-info text-uppercase fw-semibold small mb-2">Layanan kunjungan instansi</p>
                <h1 class="display-5 fw-bold mb-3">Selamat datang di SI Kunjung Pemda</h1>
                <p class="lead text-white-50 mb-4">Informasi jadwal layanan dan kegiatan instansi dalam satu tempat.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light px-4" href="{{ route('jadwal') }}">Lihat jadwal</a>
                    @guest
                        <a class="btn btn-outline-light px-4" href="{{ route('login') }}">Masuk ke akun</a>
                    @elseif (auth()->user()->role === \App\Models\User::ROLE_PEMOHON)
                        <a class="btn btn-outline-light px-4" href="{{ route('visits.create') }}">Ajukan kunjungan</a>
                        <a class="btn btn-outline-light px-4" href="{{ route('visits.index') }}">Pengajuan saya</a>
                    @elseif (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                        <a class="btn btn-outline-light px-4" href="{{ route('admin.index') }}">Manajemen sistem</a>
                    @elseif (auth()->user()->role === \App\Models\User::ROLE_KARYAWAN)
                        <a class="btn btn-outline-light px-4" href="{{ route('employee.workspace') }}">Ruang kerja</a>
                    @else
                        <a class="btn btn-outline-light px-4" href="{{ route('dashboard') }}">Buka dashboard</a>
                    @endguest
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block">
                <div class="p-4 rounded-4 border border-light border-opacity-25 bg-white bg-opacity-10">
                    <div class="small text-white-50 mb-2">Jadwal aktif mendatang</div>
                    <div class="display-4 fw-bold">{{ $jadwalAktif }}</div>
                    <div class="small text-white-50">tersedia untuk dilihat publik</div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-5" aria-label="Ringkasan layanan">
        @foreach ([
            ['label' => 'Karyawan', 'value' => $totalKaryawan, 'detail' => 'profil terdaftar'],
            ['label' => 'Jadwal aktif', 'value' => $jadwalAktif, 'detail' => 'layanan mendatang'],
            ['label' => 'Kegiatan', 'value' => $totalKegiatan, 'detail' => 'agenda instansi'],
            ['label' => 'Disetujui', 'value' => $kunjunganDisetujui, 'detail' => 'kunjungan terkonfirmasi'],
        ] as $stat)
            <div class="col-6 col-xl-3">
                <article class="surface h-100 p-3 p-md-4 d-flex align-items-center gap-3">
                    <span class="stat-mark" aria-hidden="true"></span>
                    <div>
                        <div class="stat-value">{{ $stat['value'] }}</div>
                        <div class="fw-semibold mt-2">{{ $stat['label'] }}</div>
                        <div class="text-secondary small">{{ $stat['detail'] }}</div>
                    </div>
                </article>
            </div>
        @endforeach
    </section>

    <section class="row g-4">
        <div class="col-lg-7">
            <div class="surface p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <p class="eyebrow mb-1">Mendatang</p>
                        <h2 class="section-title h4 mb-0">Jadwal kunjungan</h2>
                    </div>
                    <a class="link-success text-decoration-none small fw-semibold" href="{{ route('jadwal') }}">Semua jadwal &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr><th>Tanggal</th><th>Waktu</th><th>Karyawan</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($jadwalTerbaru as $jadwal)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y') }}</td>
                                    <td>{{ substr($jadwal->jam_aktif, 0, 5) }}–{{ substr($jadwal->jam_non_aktif, 0, 5) }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $jadwal->nama }}</div>
                                        <div class="text-secondary small">{{ $jadwal->jabatan }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="text-secondary py-4" colspan="3">Belum ada jadwal aktif mendatang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="surface p-3 p-md-4 h-100">
                <p class="eyebrow mb-1">Agenda instansi</p>
                <h2 class="section-title h4 mb-3">Kegiatan terbaru</h2>
                <div class="d-flex flex-column gap-3">
                    @forelse ($kegiatanTerbaru as $kegiatan)
                        <article class="border-bottom pb-3">
                            <div class="small text-secondary">
                                {{ \Illuminate\Support\Carbon::parse($kegiatan->tanggal_awal)->translatedFormat('d M Y') }}
                                — {{ \Illuminate\Support\Carbon::parse($kegiatan->tanggal_akhir)->translatedFormat('d M Y') }}
                            </div>
                            <div class="fw-semibold mt-1">{{ $kegiatan->deskripsi_kegiatan }}</div>
                            <div class="small text-secondary">{{ $kegiatan->nama }} · {{ $kegiatan->jabatan }}</div>
                        </article>
                    @empty
                        <p class="text-secondary mb-0">Belum ada agenda yang ditampilkan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="surface p-3 p-md-4 mt-4">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <p class="eyebrow mb-1">Tim layanan</p>
                <h2 class="section-title h4 mb-0">Karyawan instansi</h2>
            </div>
            <a class="link-success text-decoration-none small fw-semibold" href="{{ route('struktur') }}">Lihat struktur &rarr;</a>
        </div>
        <div class="row g-3">
            @forelse ($pimpinan as $orang)
                <div class="col-sm-6 col-lg-3">
                    <div class="rounded-3 bg-light p-3 h-100">
                        <div class="fw-semibold">{{ $orang->nama }}</div>
                        <div class="small text-secondary">{{ $orang->jabatan }}</div>
                    </div>
                </div>
            @empty
                <p class="text-secondary mb-0">Data karyawan belum tersedia.</p>
            @endforelse
        </div>
    </section>
@endsection
