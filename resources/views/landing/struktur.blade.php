@extends('layouts.app')

@section('title', 'Struktur Karyawan')
@section('page-title', 'Struktur karyawan')

@section('content')
    <div class="mb-4">
        <p class="eyebrow mb-1">Tim instansi</p>
        <h1 class="section-title h2 mb-1">Karyawan SI Kunjung Pemda</h1>
        <p class="text-secondary mb-0">Informasi karyawan yang melayani kunjungan.</p>
    </div>

    <div class="row g-3">
        @forelse ($karyawans as $karyawan)
            <div class="col-sm-6 col-lg-4">
                <article class="surface p-4 h-100">
                    <div class="eyebrow mb-2">{{ $karyawan->jabatan }}</div>
                    <h2 class="h5 fw-bold mb-2">{{ $karyawan->nama }}</h2>
                    <p class="text-secondary small mb-0">
                        {{ $karyawan->no_telepon ?: 'Informasi kontak tidak tersedia' }}
                    </p>
                </article>
            </div>
        @empty
            <div class="col">
                <div class="surface p-5 text-center text-secondary">Data karyawan belum tersedia.</div>
            </div>
        @endforelse
    </div>
@endsection
