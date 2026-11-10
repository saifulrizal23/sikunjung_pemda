@extends('layouts.app')

@section('title', 'Ajukan kunjungan')
@section('page-title', 'Ajukan kunjungan')

@section('content')
    <section class="mb-4">
        <p class="eyebrow mb-2">Layanan pemohon</p>
        <h1 class="section-title h2 mb-2">Ajukan kunjungan</h1>
        <p class="text-secondary mb-0">Pilih jadwal aktif dan waktu yang masih tersedia.</p>
    </section>

    <section class="surface p-3 p-md-4">
        @if ($schedules->isEmpty())
            <div class="alert alert-info mb-0">Belum ada jadwal layanan aktif yang dapat diajukan.</div>
        @else
            <form method="POST" action="{{ route('visits.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="schedule_id">Jadwal layanan</label>
                    <select class="form-select @error('schedule_id') is-invalid @enderror" id="schedule_id" name="schedule_id" required>
                        <option value="">Pilih karyawan dan tanggal</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->id }}" @selected((string) old('schedule_id') === (string) $schedule->id)>
                                {{ $schedule->tanggal->translatedFormat('l, d M Y') }} · {{ $schedule->karyawan->nama }} ({{ $schedule->jabatan }}) · {{ substr($schedule->jam_aktif, 0, 5) }}–{{ substr($schedule->jam_non_aktif, 0, 5) }}
                            </option>
                        @endforeach
                    </select>
                    @error('schedule_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="jam">Waktu kunjungan</label>
                    <input class="form-control @error('jam') is-invalid @enderror" id="jam" name="jam" type="time" value="{{ old('jam') }}" required>
                    @error('jam')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold" for="keperluan">Keperluan kunjungan</label>
                    <textarea class="form-control @error('keperluan') is-invalid @enderror" id="keperluan" name="keperluan" rows="4" maxlength="255" required>{{ old('keperluan') }}</textarea>
                    @error('keperluan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('visits.index') }}">Kembali</a>
                    <button class="btn btn-teal px-4" type="submit">Kirim pengajuan</button>
                </div>
            </form>
        @endif
    </section>
@endsection
