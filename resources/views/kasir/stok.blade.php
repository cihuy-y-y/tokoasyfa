@extends('layouts.app')

@section('title', 'Stok Barang')

@push('styles')
    {{-- dashboard.css = kartu & panel, barang.css = tabel/badge/pagination, kasir-stok.css = khusus halaman ini --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barang.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kasir-stok.css') }}">
@endpush

@section('content')
@php
    // Query string dasar untuk link tab/urutan, supaya filter lain ikut terbawa
    $dasar = request()->except(['page', 'kategori']);
    $tabAktif = count($dipilih) === 1 ? $dipilih[0] : (count($dipilih) === 0 ? '' : null);
@endphp

{{-- Judul --}}
<div class="page-bar">
    <div class="page-title">
        <span class="page-ico round">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>
            </svg>
        </span>
        <div>
            <h1>Stok Barang</h1>
            <p>Lihat ketersediaan stok produk di toko.</p>
        </div>
    </div>
    <span class="stok-clock" id="stokClock">-</span>
</div>

{{-- Ringkasan --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon ic-pink">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Total Produk</span>
            <strong class="stat-value">{{ number_format($summary['total'], 0, ',', '.') }}</strong>
            <span class="stat-note">Semua produk tersedia</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/></svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Stok Menipis</span>
            <strong class="stat-value">{{ $summary['menipis'] }}</strong>
            <span class="stat-note">Perlu segera ditambah</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Stok Aman</span>
            <strong class="stat-value">{{ number_format($summary['aman'], 0, ',', '.') }}</strong>
            <span class="stat-note">Stok dalam kondisi baik</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/></svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Produk Habis</span>
            <strong class="stat-value">{{ $summary['habis'] }}</strong>
            <span class="stat-note">Segera lakukan restok</span>
        </div>
    </div>
</div>

<div class="stok-grid">

    {{-- ============ DAFTAR STOK ============ --}}
    <section class="panel">
        <div class="panel-head">
            <h2 class="h-sm">Daftar Stok Barang</h2>
            <form method="GET" action="{{ route('kasir.stok') }}" class="urut-form">
                @foreach ($dipilih as $k) <input type="hidden" name="kategori[]" value="{{ $k }}"> @endforeach
                @if (request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                <select name="urut" class="range-select" onchange="this.form.submit()">
                    <option value="">Urutkan: Terbaru</option>
                    <option value="nama"  @selected(request('urut') === 'nama')>Urutkan: Nama</option>
                    <option value="stok"  @selected(request('urut') === 'stok')>Urutkan: Stok Terendah</option>
                    <option value="harga" @selected(request('urut') === 'harga')>Urutkan: Harga Tertinggi</option>
                </select>
            </form>
        </div>

        {{-- Tab kategori --}}
        <div class="chips">
            <a href="{{ route('kasir.stok', $dasar) }}" class="chip {{ $tabAktif === '' ? 'active' : '' }}">Semua Produk</a>
            @foreach ($kategoriList as $k)
                <a href="{{ route('kasir.stok', $dasar + ['kategori' => [$k]]) }}"
                   class="chip {{ $tabAktif === $k ? 'active' : '' }}">{{ $k }}</a>
            @endforeach
        </div>

        <div class="table-wrap">
            <table class="data-table stok-table">
                <thead>
                    <tr>
                        <th>No</th><th>Gambar</th><th>Nama Produk</th><th>Kategori</th>
                        <th>Harga</th><th>Stok</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($products as $p)
                    <tr>
                        <td>{{ $products->firstItem() + $loop->index }}</td>
                        <td>
                            <span class="p-thumb sm">
                                @if ($p->gambar && file_exists(public_path('images/produk/' . $p->gambar)))
                                    <img src="{{ asset('images/produk/' . $p->gambar) }}" alt="{{ $p->nama }}">
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
                                @endif
                            </span>
                        </td>
                        <td>
                            <div class="p-name">{{ $p->nama }}</div>
                            <small class="p-sku">SKU : {{ $p->kode }}</small>
                        </td>
                        <td><span class="cat cat-{{ \Illuminate\Support\Str::slug($p->kategori) }}">{{ $p->kategori }}</span></td>
                        <td>Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                        <td><strong>{{ $p->stok }}</strong></td>
                        <td><span class="st st-{{ $p->status }}">{{ $p->status_label }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">Produk tidak ditemukan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-foot">
            <span class="foot-info">
                Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk
            </span>
            {{ $products->links('partials.pagination') }}
        </div>
    </section>

    {{-- ============ PANEL KANAN ============ --}}
    <aside class="stok-side">

        {{-- Cari + filter kategori (satu form) --}}
        <form method="GET" action="{{ route('kasir.stok') }}" class="panel side-card">
            @if (request('urut')) <input type="hidden" name="urut" value="{{ request('urut') }}"> @endif

            <h3>Cari Produk</h3>
            <div class="side-search">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama produk atau kode...">
                <button type="submit">Cari</button>
            </div>

            <h3>Filter Kategori</h3>
            <div class="side-check">
                <label>
                    <input type="checkbox" id="chkSemua" @checked(count($dipilih) === 0)> Semua Kategori
                </label>
                @foreach ($kategoriList as $k)
                    <label>
                        <input type="checkbox" name="kategori[]" value="{{ $k }}" class="chk-kat" @checked(in_array($k, $dipilih, true))> {{ $k }}
                    </label>
                @endforeach
            </div>

            <div class="side-actions">
                <button type="submit" class="btn-terapkan">Terapkan</button>
                <a href="{{ route('kasir.stok') }}" class="btn-reset-sm">Reset</a>
            </div>
        </form>

        {{-- Produk menipis --}}
        <div class="panel side-card">
            <div class="panel-head tight">
                <h3>Produk Stok Menipis</h3>
            </div>

            <ul class="low-list">
                @forelse ($menipisList as $p)
                    <li>
                        <span class="p-thumb xs">
                            @if ($p->gambar && file_exists(public_path('images/produk/' . $p->gambar)))
                                <img src="{{ asset('images/produk/' . $p->gambar) }}" alt="">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
                            @endif
                        </span>
                        <span class="low-name">{{ $p->nama }}</span>
                        <span class="low-stok {{ $p->stok <= 0 ? 'zero' : '' }}">{{ $p->stok <= 0 ? 'Habis' : 'Stok: ' . $p->stok }}</span>
                    </li>
                @empty
                    <li class="low-empty">Semua stok aman 🎉</li>
                @endforelse
            </ul>
        </div>

        {{-- Tips --}}
        <div class="side-tip">
            <span class="tip-ico">💡</span>
            <div>
                <strong>Pastikan stok barang selalu tersedia!</strong>
                <p>Stok yang cukup akan membuat pelanggan tetap puas dan memudahkan proses transaksi.</p>
            </div>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    // Jam
    function jam() {
        const d = new Date(), p = n => String(n).padStart(2, '0');
        document.getElementById('stokClock').textContent =
            `${p(d.getDate())}/${p(d.getMonth() + 1)}/${d.getFullYear()} · ${p(d.getHours())}:${p(d.getMinutes())}`;
    }
    jam(); setInterval(jam, 20000);

    // "Semua Kategori" menghapus centang kategori lain, dan sebaliknya
    const semua = document.getElementById('chkSemua');
    const kats  = [...document.querySelectorAll('.chk-kat')];

    semua.addEventListener('change', () => { if (semua.checked) kats.forEach(c => c.checked = false); else semua.checked = true; });
    kats.forEach(c => c.addEventListener('change', () => {
        semua.checked = !kats.some(x => x.checked);
    }));
</script>
@endpush