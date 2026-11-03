@php
    $user    = auth()->user();
    $inisial = strtoupper(mb_substr($user->name, 0, 1));
@endphp

<header class="header">
    <button type="button" class="burger" id="burger" aria-label="Buka menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- Pencarian --}}
    <form class="search" action="#" method="GET">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
        </svg>
        <input type="text" name="q" placeholder="Cari barang, kategori, atau transaksi...">
    </form>

    <div class="header-right">
        {{-- Notifikasi --}}
        <button type="button" class="icon-btn" aria-label="Notifikasi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.9 1.9 0 0 0 3.4 0"/>
            </svg>
            <span class="dot"></span>
        </button>

        {{-- Profil --}}
        <div class="profile" id="profile">
            <button type="button" class="profile-btn" id="profileBtn">
                <span class="avatar">{{ $inisial }}</span>
                <span class="profile-name">{{ $user->name }}</span>
                <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </button>

            <div class="dropdown" id="dropdown">
                <div class="dropdown-info">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ ucfirst($user->role) }} &middot; {{ $user->username }}</small>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>