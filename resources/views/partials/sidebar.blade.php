@php
    $role = auth()->user()->role;

    // 'route' => null artinya halamannya belum dibuat (link sementara '#')
    $menus = [
        ['label' => 'Dashboard',   'route' => 'admin.dashboard', 'roles' => ['owner', 'admin'],
         'icon'  => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>'],
        ['label' => 'Data Barang', 'route' => 'barang.index', 'roles' => ['owner', 'admin'],
         'icon'  => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>'],
        ['label' => 'Transaksi',   'route' => null, 'roles' => ['owner', 'admin'],
         'icon'  => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/>'],
        ['label' => 'Laporan',     'route' => null, 'roles' => ['owner', 'admin'],
         'icon'  => '<path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>'],
        ['label' => 'Pengguna',    'route' => 'owner.users', 'roles' => ['owner'], 'match' => 'owner.*',
         'icon'  => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
        ['label' => 'Pengaturan',  'route' => null, 'roles' => ['owner', 'admin'],
         'icon'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3h0a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5h0a1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8v0a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>'],
    ];
@endphp

<aside class="sidebar" id="sidebar">
    <a href="{{ route('home') }}" class="brand">
        <span class="brand-top">Toko Kosmetik</span>
        <span class="brand-name">Asyfa</span>
    </a>

    <nav class="menu">
        @foreach ($menus as $m)
            @continue(! in_array($role, $m['roles']))

            @php
                $href   = $m['route'] ? route($m['route']) : '#';
                $active = $m['route'] && request()->routeIs($m['match'] ?? $m['route']);
            @endphp

            <a href="{{ $href }}" class="menu-item {{ $active ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $m['icon'] !!}</svg>
                <span>{{ $m['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <form action="{{ route('logout') }}" method="POST" class="logout-form">
        @csrf
        <button type="submit" class="menu-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
            <span>Logout</span>
        </button>
    </form>
</aside>