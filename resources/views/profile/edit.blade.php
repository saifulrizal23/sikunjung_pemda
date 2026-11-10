@extends('layouts.app')

@section('title', 'Profil saya')
@section('page-title', 'Profil saya')

@section('content')
    <section class="mb-4">
        <p class="eyebrow mb-2">Pengaturan akun</p>
        <h1 class="section-title h2 mb-2">Profil saya</h1>
        <p class="text-secondary mb-0">Perbarui informasi akun dan kata sandi Anda.</p>
    </section>

    <section class="surface p-3 p-md-4">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <h2 class="section-title h5 mb-3">Informasi akun</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="name">Nama lengkap</label>
                    <input
                        class="form-control @error('name') is-invalid @enderror"
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        autocomplete="name"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="email">Alamat email</label>
                    <input
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $user->email) }}"
                        autocomplete="email"
                        required
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <h2 class="section-title h5 mb-1">Ubah kata sandi</h2>
            <p class="text-secondary small mb-3">Kosongkan bagian ini jika tidak ingin mengganti kata sandi.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="current_password">Kata sandi saat ini</label>
                    <input
                        class="form-control @error('current_password') is-invalid @enderror"
                        id="current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                    >
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="password">Kata sandi baru</label>
                    <input
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="password_confirmation">Konfirmasi kata sandi baru</label>
                    <input
                        class="form-control"
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                    >
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Batal</a>
                <button class="btn btn-teal px-4" type="submit">Simpan perubahan</button>
            </div>
        </form>
    </section>
@endsection
