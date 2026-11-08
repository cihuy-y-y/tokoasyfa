<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Asyfa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    @stack('styles')
    @include('partials.theme')
</head>
<body>
<div class="app">

    @include('partials.sidebar')

    <div class="main">
        @include('partials.header')

        <main class="content">
            @yield('content')
        </main>

        @include('partials.footer')
    </div>
</div>

<div class="overlay" id="overlay"></div>

<script src="{{ asset('js/layout.js') }}"></script>
@stack('scripts')
</body>
</html>