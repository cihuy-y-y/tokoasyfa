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
        @php
            $notifIkon = [
                'stok'      => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/>',
                'transaksi' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/>',
                'laporan'   => '<path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>',
                'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
            ];
        @endphp
            
        <div class="notif" id="notif">
            <button type="button" class="icon-btn" id="notifBtn" aria-label="Notifikasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.9 1.9 0 0 0 3.4 0"/>
                </svg>
                @if ($notifs['baru'] > 0)
                    <span class="badge-count">{{ $notifs['baru'] > 9 ? '9+' : $notifs['baru'] }}</span>
                @endif
            </button>
        
            <div class="notif-panel" id="notifPanel">
                <div class="notif-head">
                    <strong>Notifikasi</strong>
                    @if ($notifs['baru'] > 0)
                        <form action="{{ route('notifikasi.baca') }}" method="POST">
                            @csrf
                            <button type="submit" class="notif-read">Tandai semua dibaca</button>
                        </form>
                    @endif
                </div>
            
                <ul class="notif-list">
                    @forelse ($notifs['items'] as $n)
                        <li>
                            <a href="{{ $n['url'] }}" class="notif-item {{ $n['baru'] ? 'unread' : '' }}">
                                <span class="notif-ico ni-{{ $n['ikon'] }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $notifIkon[$n['ikon']] !!}</svg>
                                </span>
                                <span class="notif-text">
                                    <strong>{{ $n['judul'] }}</strong>
                                    <small>{{ $n['pesan'] }}</small>
                                    <em>{{ $n['waktu'] }}</em>
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="notif-empty">Tidak ada notifikasi.</li>
                    @endforelse
                </ul>
            </div>
        </div>

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