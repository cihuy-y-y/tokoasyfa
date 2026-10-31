<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Toko Kosmetik Asya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
<div class="login-wrapper">

    <div class="login-banner" style="background-image: url('{{ asset('images/login-banner.png') }}')"></div>

    <div class="login-form-side">
        <div class="login-box">
            <h1>Buat Akun </h1>
            <p class="subtitle compact">Isi data berikut untuk mendaftar</p>

            <form action="{{ route('register.process') }}" method="POST" novalidate>
                @csrf

                {{-- Nama lengkap --}}
                <label for="name">Nama Lengkap</label>
                <div class="input-group compact @error('name') has-error @enderror">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
                    </svg>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" autofocus>
                </div>
                @error('name') <small class="error-text">{{ $message }}</small> @enderror

                {{-- Username --}}
                <label for="username">Username</label>
                <div class="input-group compact @error('username') has-error @enderror">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/><path d="M16 12v1.5a2.5 2.5 0 0 0 5 0V12a9 9 0 1 0-3.5 7.1"/>
                    </svg>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username">
                </div>
                @error('username') <small class="error-text">{{ $message }}</small> @enderror

                {{-- Email --}}
                <label for="email">Email</label>
                <div class="input-group compact @error('email') has-error @enderror">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
                    </svg>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email">
                </div>
                @error('email') <small class="error-text">{{ $message }}</small> @enderror

                {{-- Password --}}
                <label for="password">Password</label>
                <div class="input-group compact @error('password') has-error @enderror">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                    </svg>
                    <input type="password" id="password" name="password" placeholder="Minimal 8 karakter">
                    <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password') <small class="error-text">{{ $message }}</small> @enderror

                {{-- Konfirmasi password --}}
                <label for="password_confirmation">Konfirmasi Password</label>
                <div class="input-group compact">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                    </svg>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password">
                    <button type="button" class="toggle-password" data-target="password_confirmation" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>

                <button type="submit" class="btn-login">Daftar</button>
            </form>

            <p class="switch-text">Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></p>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
</script>
</body>
</html>