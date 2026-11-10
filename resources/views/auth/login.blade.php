@extends('layouts.public')

@section('title', 'Masuk')

@section('content')
    <section class="login-card">
        <div class="text-center mb-4">
            <span class="brand-mark mx-auto mb-3">SK</span>
            <p class="eyebrow mb-2">SI Kunjung Pemda</p>
            <h1 class="section-title h3 mb-2">Masuk ke akun</h1>
            <p class="text-secondary mb-0">Silakan masuk untuk melanjutkan.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold" for="email">Alamat email</label>
                <input
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="username"
                    placeholder="nama@email.go.id"
                    required
                    autofocus
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="password">Kata sandi</label>
                <input
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi"
                    required
                >
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                <label class="form-check-label text-secondary" for="remember">Ingat saya</label>
            </div>
            <button class="btn btn-teal btn-lg w-100" type="submit">Masuk</button>
        </form>
    </section>
@endsection
