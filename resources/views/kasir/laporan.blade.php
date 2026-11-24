@extends('layouts.app')

@section('title', 'Laporan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barang.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kasir-laporan.css') }}">
@endpush

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

<div class="page-bar">
    <div class="page-title">
        <span class="page-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>
            </svg>
        </span>
        <div>
            <h1>Laporan Hari Ini</h1>
            <p>Daftar transaksi Anda pada {{ now()->locale('id')->translatedFormat('l, j F Y') }}. Laporan direset otomatis setiap hari.</p>
        </div>
    </div>

    <div class="day-sum">
        <div><small>Transaksi Lunas</small><strong>{{ $jumlah }}</strong></div>
        <div><small>Total Penjualan</small><strong>{{ $rp($total) }}</strong></div>
    </div>
</div>

@if ($errors->any()) <div class="alert alert-error">{{ $errors->first() }}</div> @endif

<section class="panel">
    <div class="panel-head">
        <h2 class="h-sm">Daftar Transaksi</h2>
    </div>

    <form method="GET" action="{{ route('kasir.laporan') }}" class="filter-bar">
        <div class="f-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari no. transaksi atau nama barang...">
        </div>

        <select name="metode" onchange="this.form.submit()">
            <option value="">Semua Metode</option>
            @foreach ($metodeList as $m)
                <option value="{{ $m }}" @selected(request('metode') === $m)>{{ $m }}</option>
            @endforeach
        </select>

        <select name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            @foreach ($statusList as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>

        <a href="{{ route('kasir.laporan') }}" class="btn-reset">↻ Reset</a>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jam</th>
                    <th>No. Transaksi</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                    <th>Metode Pembayaran</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($transaksi as $t)
                @php
                    $first  = $t->items->first();
                    $lain   = $t->items->count() - 1;
                    $gambar = $first?->product?->gambar;
                    $detail = [
                        'kode'   => $t->kode,
                        'tanggal'=> $t->tanggal->format('d/m/Y H:i:s'),
                        'metode' => $t->metode,
                        'status' => $statusList[$t->status] ?? $t->status,
                        'total'  => $t->total,
                        'items'  => $t->items->map(fn ($i) => [
                            'nama' => $i->nama_produk, 'harga' => $i->harga,
                            'jumlah' => $i->jumlah, 'subtotal' => $i->subtotal,
                        ])->values(),
                    ];
                @endphp
                <tr>
                    <td>{{ $transaksi->firstItem() + $loop->index }}</td>
                    <td><span class="jam">{{ $t->tanggal->format('H:i:s') }}</span></td>
                    <td><strong>{{ $t->kode }}</strong></td>
                    <td>
                        <div class="u-cell">
                            <span class="p-thumb sm">
                                @if ($gambar && file_exists(public_path('images/produk/' . $gambar)))
                                    <img src="{{ asset('images/produk/' . $gambar) }}" alt="">
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
                                @endif
                            </span>
                            <div>
                                <div class="p-name">{{ $first->nama_produk ?? '-' }}</div>
                                @if ($lain > 0) <small class="p-sku">+{{ $lain }} barang lainnya</small> @endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $t->items->sum('jumlah') }} pcs</td>
                    <td>{{ $rp($t->total) }}</td>
                    <td>{{ $t->metode }}</td>
                    <td><span class="st st-{{ $t->status }}">{{ $statusList[$t->status] ?? $t->status }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="act btn-view" title="Lihat detail" data-detail="{{ json_encode($detail) }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <a href="{{ route('pos.struk', ['transaction' => $t, 'cetak' => 1]) }}" target="_blank" class="act" title="Cetak struk">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-2M6 14h12v7H6z"/></svg>
                            </a>
                            @if ($t->status === 'menunggu' && $t->kadaluarsa_at && $t->kadaluarsa_at->isFuture())
                                <a href="{{ route('pos.index', ['lanjut' => $t->id]) }}" class="act" title="Lanjutkan pembayaran">💳</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="empty">Belum ada transaksi hari ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-foot">
        <span class="foot-info">
            Menampilkan {{ $transaksi->firstItem() ?? 0 }} - {{ $transaksi->lastItem() ?? 0 }} dari {{ $transaksi->total() }} data
        </span>
        {{ $transaksi->links('partials.pagination') }}
    </div>
</section>

{{-- Modal detail --}}
<div class="modal" id="detailModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="dKode">Detail Transaksi</h3>
            <button type="button" class="modal-close" data-close>&times;</button>
        </div>

        <dl class="detail-meta">
            <div><dt>Waktu</dt><dd id="dTanggal"></dd></div>
            <div><dt>Metode</dt><dd id="dMetode"></dd></div>
            <div><dt>Status</dt><dd id="dStatus"></dd></div>
        </dl>

        <div class="table-wrap">
            <table class="data-table" style="min-width:0">
                <thead><tr><th>Barang</th><th>Harga</th><th>Jml</th><th>Subtotal</th></tr></thead>
                <tbody id="dItems"></tbody>
            </table>
        </div>

        <div class="detail-total">Total <strong id="dTotal"></strong></div>

        <div class="modal-foot">
            <button type="button" class="btn-cancel" data-close>Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const modal = document.getElementById('detailModal');
    const tutup = () => modal.classList.remove('show');

    document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', tutup));
    modal.addEventListener('click', e => { if (e.target === modal) tutup(); });

    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = JSON.parse(btn.dataset.detail);

            document.getElementById('dKode').textContent    = d.kode;
            document.getElementById('dTanggal').textContent = d.tanggal;
            document.getElementById('dMetode').textContent  = d.metode;
            document.getElementById('dStatus').textContent  = d.status;
            document.getElementById('dTotal').textContent   = rp(d.total);

            const tbody = document.getElementById('dItems');
            tbody.innerHTML = '';
            d.items.forEach(i => {
                const tr = document.createElement('tr');
                [i.nama, rp(i.harga), i.jumlah, rp(i.subtotal)].forEach(v => {
                    const td = document.createElement('td');
                    td.textContent = v;
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });

            modal.classList.add('show');
        });
    });
</script>
@endpush