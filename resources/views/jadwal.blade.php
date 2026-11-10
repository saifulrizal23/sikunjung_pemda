@extends('layouts.app')

@section('title', 'Jadwal Layanan')
@section('page-title', 'Jadwal layanan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Informasi publik</p>
            <h1 class="section-title h2 mb-1">Jadwal layanan kunjungan</h1>
            <p class="text-secondary mb-0">Temukan waktu layanan dan karyawan yang dapat ditemui.</p>
        </div>
        <span class="badge rounded-pill badge-soft px-3 py-2">{{ $settingKunjungans->count() }} jadwal</span>
    </div>

    <form class="surface p-3 mb-4" method="GET" action="{{ route('jadwal') }}">
        <div class="row g-2 align-items-center">
            <div class="col-md">
                <label class="visually-hidden" for="search">Cari jadwal</label>
                <input class="form-control" id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIK, atau jabatan">
            </div>
            <div class="col-sm-5 col-md-3">
                <label class="visually-hidden" for="status">Status jadwal</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Jadwal aktif</option>
                    <option value="Y" @selected(request('status') === 'Y')>Aktif</option>
                    <option value="T" @selected(request('status') === 'T')>Nonaktif</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <button class="btn btn-teal px-4 w-100" type="submit">Cari jadwal</button>
            </div>
        </div>
    </form>

    <section class="surface p-2 p-md-3 mb-5">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>Tanggal</th><th>Jam layanan</th><th>Karyawan</th><th>Jabatan</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse ($settingKunjungans as $jadwal)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d M Y') }}</td>
                            <td>{{ substr($jadwal->jam_aktif, 0, 5) }}–{{ substr($jadwal->jam_non_aktif, 0, 5) }}</td>
                            <td class="fw-semibold">{{ $jadwal->nama }}</td>
                            <td>{{ $jadwal->jabatan }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $jadwal->status === 'Y' ? 'badge-soft' : 'text-bg-secondary' }}">
                                    {{ $jadwal->status === 'Y' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="text-secondary text-center py-5" colspan="5">Tidak ada jadwal yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <p class="eyebrow mb-1">Agenda instansi</p>
        <h2 class="section-title h3 mb-3">Kegiatan dinas</h2>
        <div class="row g-3">
            @forelse ($kegiatans as $kegiatan)
                <div class="col-md-6 col-xl-4">
                    <article class="surface p-4 h-100">
                        <div class="small text-secondary mb-2">
                            {{ \Illuminate\Support\Carbon::parse($kegiatan->tanggal_awal)->translatedFormat('d M Y') }}
                            – {{ \Illuminate\Support\Carbon::parse($kegiatan->tanggal_akhir)->translatedFormat('d M Y') }}
                        </div>
                        <h3 class="h5 fw-bold">{{ $kegiatan->deskripsi_kegiatan }}</h3>
                        <p class="text-secondary mb-0">{{ $kegiatan->nama }} · {{ $kegiatan->jabatan }}</p>
                    </article>
                </div>
            @empty
                <div class="col"><p class="text-secondary">Belum ada kegiatan.</p></div>
            @endforelse
        </div>
    </section>
@endsection
