@php $tema = \App\Models\Setting::tema(); @endphp
<style>
    :root {
        --pink: {{ $tema['main'] }};
        --pink-dark: {{ $tema['dark'] }};
        --pink-soft: {{ $tema['soft'] }};
    }
    .brand-img { max-width: 180px; max-height: 90px; object-fit: contain; }
</style>
@if (\App\Models\Setting::ambil('mode_tampilan') === 'gelap')
    <link rel="stylesheet" href="{{ asset('css/dark.css') }}">
@endif