@php
    use Illuminate\Support\Facades\Route;

    $logoToko = \App\Models\Setting::ambil('logo');

    // Route yang belum ada otomatis jadi '#'
    $menus = [
        ['label' => 'Dashboard',   'route' => 'kasir.dashboard', 'match' => 'kasir.dashboard',
         'icon'  => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>'],
        ['label' => 'Transaksi',   'route' => 'pos.index', 'match' => 'pos.*',
         'icon'  => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>'],
        ['label' => 'Stok Barang', 'route' => 'kasir.stok', 'match' => 'kasir.stok*',
         'icon'  => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>'],
        ['label' => 'Laporan',     'route' => 'kasir.laporan', 'match' => 'kasir.laporan*',
         'icon'  => '<path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>'],
    ];
@endphp

<aside class="sidebar" id="sidebar">
    <a href="{{ route('kasir.dashboard') }}" class="brand">
        @if ($logoToko && file_exists(public_path('images/toko/' . $logoToko)))
            <img src="{{ asset('images/toko/' . $logoToko) }}" alt="{{ \App\Models\Setting::ambil('nama_toko') }}" class="brand-img">
        @else
            <span class="brand-top">Toko Kosmetik</span>
            <span class="brand-name">Asyfa</span>
        @endif
    </a>

    <nav class="menu">
        @foreach ($menus as $m)
            @php
                $ada    = Route::has($m['route']);
                $href   = $ada ? route($m['route']) : '#';
                $active = $ada && request()->routeIs($m['match']);
            @endphp

            <a href="{{ $href }}" class="menu-item {{ $active ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $m['icon'] !!}</svg>
                <span>{{ $m['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <p class="side-quote">Cantik<br>Setiap Hari<br>Bersama Asyfa <span>♥</span></p>

    <form action="{{ route('logout') }}" method="POST" data-turbo="false" class="logout-form">
        @csrf
        <button type="submit" class="menu-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
            <span>Logout</span>
        </button>
    </form>
</aside>