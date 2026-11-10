@extends('layouts.app')

@section('title', 'Pengajuan kunjungan')
@section('page-title', 'Pengajuan kunjungan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Area pemohon</p>
            <h1 class="section-title h2 mb-1">Pengajuan kunjungan saya</h1>
            <p class="text-secondary mb-0">Pantau status dan riwayat pengajuan Anda.</p>
        </div>
        <a class="btn btn-teal" href="{{ route('visits.create') }}">Buat pengajuan</a>
    </div>

    <section class="surface p-2 p-md-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>Tanggal</th><th>Waktu</th><th>Karyawan</th><th>Keperluan</th><th>Status</th><th>Catatan</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($kunjungans as $kunjungan)
                        <tr>
                            <td>{{ $kunjungan->tanggal->translatedFormat('d M Y') }}</td>
                            <td>{{ substr($kunjungan->jam, 0, 5) }}</td>
                            <td>{{ $kunjungan->karyawan->nama }}</td>
                            <td>{{ $kunjungan->keperluan }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $kunjungan->status === 'disetujui' ? 'badge-soft' : ($kunjungan->status === 'ditolak' ? 'text-bg-danger' : 'text-bg-warning') }}">
                                    {{ ucfirst($kunjungan->status) }}
                                </span>
                            </td>
                            <td>{{ $kunjungan->catatan ?: '—' }}</td>
                            <td>
                                @if ($kunjungan->status === \App\Models\Kunjungan::STATUS_MENUNGGU)
                                    <form method="POST" action="{{ route('visits.destroy', $kunjungan) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-secondary btn-sm" type="submit">Batalkan</button>
                                    </form>
                                @else
                                    <span class="text-secondary small">Selesai diproses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="text-center text-secondary py-5" colspan="7">Anda belum memiliki pengajuan kunjungan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
