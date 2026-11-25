<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="turbo-cache-control" content="no-cache">
    <title>Toko Asyfa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/confirm.css') }}">
    @stack('styles')
    @include('partials.theme')

    {{-- Helper: jalankan script halaman setelah Turbo selesai menampilkan halaman --}}
    <script>
        window.onPage = function (fn) {
            document.addEventListener('turbo:load', fn, { once: true });
            // Cadangan bila Turbo gagal dimuat (mis. CDN tidak terjangkau)
            window.addEventListener('load', function () { if (!window.Turbo) fn(); }, { once: true });
        };
    </script>

    {{-- Pustaka dimuat sekali di sini (bukan di tiap halaman) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.12/dist/turbo.es2017-umd.js"></script>
    <script defer src="{{ asset('js/layout.js') }}"></script>
</head>
<body>
<div class="app">

    @include(auth()->user()->role === 'kasir' ? 'partials.sidebar-kasir' : 'partials.sidebar')

    <div class="main">
        @include('partials.header')

        <main class="content">
            @yield('content')
        </main>

        @include('partials.footer')
    </div>
</div>

<div class="overlay" id="overlay"></div>

{{-- Modal konfirmasi global pengganti confirm() browser --}}
<div class="app-confirm-overlay" id="appConfirmModal" aria-hidden="true">
    <div class="app-confirm-box" role="dialog" aria-modal="true" aria-labelledby="appConfirmTitle">
        <div class="app-confirm-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 9v4m0 4h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>
            </svg>
        </div>
        <h3 id="appConfirmTitle">Konfirmasi</h3>
        <p id="appConfirmMessage">Apakah kamu yakin ingin melanjutkan?</p>
        <div class="app-confirm-detail" id="appConfirmDetail" hidden></div>
        <div class="app-confirm-actions">
            <button type="button" class="app-confirm-cancel" id="appConfirmCancel">Batal</button>
            <button type="button" class="app-confirm-ok" id="appConfirmOk">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
