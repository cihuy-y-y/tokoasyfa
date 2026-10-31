<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Toko Kosmetik Asya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
<div class="login-wrapper">

    {{-- Sisi kiri: gambar banner --}}
    <div class="login-banner" style="background-image: url('{{ asset('images/login-banner.png') }}')"></div>

    {{-- Sisi kanan: form --}}
    <div class="login-form-side">
        <div class="login-box">
            <h1>Login</h1>
            <p class="subtitle">Silakan login untuk melanjutkan</p>

            @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('login.process') }}" method="POST" novalidate>
                @csrf

                {{-- Username --}}
                <label for="username">Username</label>
                <div class="input-group @error('username') has-error @enderror">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
                    </svg>
                    <input type="text" id="username" name="username"
                           value="{{ old('username') }}"
                           placeholder="Masukkan username" autofocus>
                </div>
                @error('username')
                    <small class="error-text">{{ $message }}</small>
                @enderror

                {{-- Password --}}
                <label for="password">Password</label>
                <div class="input-group @error('password') has-error @enderror">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                    </svg>
                    <input type="password" id="password" name="password" placeholder="Masukkan password">
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <small class="error-text">{{ $message }}</small>
                @enderror

                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <p class="switch-text">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></p>
        
        </div>
    </div>
</div>

<script>
    const toggle = document.getElementById('togglePassword');
    const input  = document.getElementById('password');
    toggle.addEventListener('click', () => {
        input.type = input.type === 'password' ? 'text' : 'password';
    });
</script>
</body>
</html>