@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@push('styles')
    {{-- dashboard.css = kartu, barang.css = tabel/filter/modal, laporan.css = khusus halaman ini --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barang.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
@endpush

@section('content')
@php
    $isOwner = auth()->user()->isOwner();
    $rp      = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $tglFmt  = fn ($d) => $d->format('Y-m-d');

    $cards = [
        ['label' => 'Total Pendapatan', 'value' => $rp($now['pendapatan']), 'tren' => $tren['pendapatan'], 'warna' => 'ic-pink',
         'icon' => '<path d="M9 3h6l-1.5 3h-3zM7 21h10a2 2 0 0 0 2-2c0-4-2-8-5-9.5h-4C7 11 5 15 5 19a2 2 0 0 0 2 2zM12 12v6M14 13.5h-3a1.2 1.2 0 0 0 0 2.4h2a1.2 1.2 0 0 1 0 2.4h-3"/>'],
        ['label' => 'Jumlah Transaksi', 'value' => number_format($now['jumlah'], 0, ',', '.'), 'tren' => $tren['jumlah'], 'warna' => 'ic-orange',
         'icon' => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>'],
        ['label' => 'Produk Terjual', 'value' => number_format($now['pcs'], 0, ',', '.') . ' pcs', 'tren' => $tren['pcs'], 'warna' => 'ic-green',
         'icon' => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>'],
        ['label' => 'Rata-rata Transaksi', 'value' => $rp($now['rata']), 'tren' => $tren['rata'], 'warna' => 'ic-purple',
         'icon' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/>'],
    ];
@endphp

{{-- Judul + periode --}}
<div class="page-bar">
    <div class="page-title">
        <span class="page-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>
            </svg>
        </span>
        <div>
            <h1>Laporan Penjualan</h1>
            <p>Ringkasan data penjualan toko kosmetik Asya dalam periode yang dipilih</p>
        </div>
    </div>

    <div class="page-tools">
        <form method="GET" action="{{ route('laporan.index') }}" class="range-form">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
            <input type="date" name="dari" value="{{ $tglFmt($dari) }}" max="{{ now()->format('Y-m-d') }}">
            <span>-</span>
            <input type="date" name="sampai" value="{{ $tglFmt($sampai) }}" max="{{ now()->format('Y-m-d') }}">
            <button type="submit" class="btn-range">Terapkan</button>
        </form>

        <a href="{{ route('laporan.export', ['dari' => $tglFmt($dari), 'sampai' => $tglFmt($sampai)]) }}" class="btn-add">⬇ Download Laporan</a>
    </div>
</div>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if ($errors->any())     <div class="alert alert-error">{{ $errors->first() }}</div>      @endif

{{-- Kartu ringkasan --}}
<div class="stat-grid">
    @foreach ($cards as $c)
        <div class="stat-card">
            <div class="stat-icon {{ $c['warna'] }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $c['icon'] !!}</svg>
            </div>
            <div class="stat-body">
                <span class="stat-label">{{ $c['label'] }}</span>
                <strong class="stat-value">{{ $c['value'] }}</strong>
                @if ($c['tren'] === null)
                    <span class="stat-note">Belum ada data periode sebelumnya</span>
                @else
                    <span class="stat-note {{ $c['tren'] >= 0 ? 'up' : 'down' }}">
                        {{ $c['tren'] >= 0 ? '▲ +' : '▼ ' }}{{ $c['tren'] }}% dari periode sebelumnya
                    </span>
                @endif
            </div>
        </div>
    @endforeach
</div>

{{-- Grafik, metode pembayaran, produk terlaris --}}
<div class="rep-grid">

    <section class="panel">
        <div class="panel-head">
            <h2 class="h-sm">Grafik Penjualan</h2>
            <span class="period-badge">{{ $dari->format('d/m/Y') }} - {{ $sampai->format('d/m/Y') }}</span>
        </div>
        <div class="chart-wrap"><canvas id="salesChart"></canvas></div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2 class="h-sm">Metode Pembayaran</h2></div>
        <div class="donut-box">
            <div class="donut-wrap">
                <canvas id="payChart"></canvas>
                <div class="donut-center">
                    <strong>{{ $totalMetode }}</strong>
                    <span>Transaksi</span>
                </div>
            </div>
            <ul class="legend">
                @foreach ($metode as $m)
                    <li>
                        <i style="background: {{ $m['warna'] }}"></i>
                        <span class="lg-name">{{ $m['nama'] }}</span>
                        <span class="lg-val">{{ $m['persen'] }}% ({{ $m['jumlah'] }})</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2 class="h-sm">Produk Terlaris</h2></div>
        <ul class="top-rep">
            @forelse ($top as $i => $p)
                @php $gambar = $gambarProduk[$p->product_id] ?? null; @endphp
                <li>
                    <span class="rank">{{ $i + 1 }}.</span>
                    <span class="thumb" style="background:#fde8ee">
                        @if ($gambar && file_exists(public_path('images/produk/' . $gambar)))
                            <img src="{{ asset('images/produk/' . $gambar) }}" alt="{{ $p->nama_produk }}">
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="#e0587f" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
                        @endif
                    </span>
                    <div class="top-info">
                        <div class="top-line">
                            <span class="top-name">{{ $p->nama_produk }}</span>
                            <span class="top-qty">{{ $p->terjual }} pcs</span>
                        </div>
                        <div class="bar"><span style="width: {{ round($p->terjual / $maxTerjual * 100) }}%"></span></div>
                    </div>
                </li>
            @empty
                <li class="empty-li">Belum ada penjualan di periode ini.</li>
            @endforelse
        </ul>
    </section>
</div>

{{-- Daftar transaksi --}}
<section class="panel">
    <div class="panel-head">
        <h2 class="h-sm">Daftar Transaksi</h2>
    </div>

    <form method="GET" action="{{ route('laporan.index') }}" class="filter-bar">
        <input type="hidden" name="dari" value="{{ $tglFmt($dari) }}">
        <input type="hidden" name="sampai" value="{{ $tglFmt($sampai) }}">

        <div class="f-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari no. transaksi, nama barang, atau kasir...">
        </div>

        <select name="kasir" onchange="this.form.submit()">
            <option value="">Semua Kasir</option>
            @foreach ($kasirList as $k)
                <option value="{{ $k->id }}" @selected(request('kasir') == $k->id)>{{ $k->name }}</option>
            @endforeach
        </select>

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

        <a href="{{ route('laporan.index', ['dari' => $tglFmt($dari), 'sampai' => $tglFmt($sampai)]) }}" class="btn-reset">↻ Reset</a>
        <a href="{{ route('laporan.export', request()->query()) }}" class="btn-export">⬇ Export</a>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No. Transaksi</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                    <th>Kasir</th>
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
                        'tanggal'=> $t->tanggal->format('d/m/Y H:i'),
                        'kasir'  => $t->kasir->name ?? '-',
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
                    <td>{{ $t->tanggal->format('d/m/Y H:i') }}</td>
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
                    <td>{{ $t->kasir->name ?? '-' }}</td>
                    <td>{{ $t->metode }}</td>
                    <td><span class="st st-{{ $t->status }}">{{ $statusList[$t->status] ?? $t->status }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="act btn-view" title="Lihat detail"
                                    data-detail="{{ json_encode($detail) }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>

                            @if ($isOwner)
                                <button type="button" class="act btn-edit" title="Edit"
                                        data-url="{{ route('laporan.update', $t) }}"
                                        data-kode="{{ $t->kode }}"
                                        data-metode="{{ $t->metode }}"
                                        data-status="{{ $t->status }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                </button>

                                <form action="{{ route('laporan.destroy', $t) }}" method="POST"
                                      onsubmit="return confirm('Hapus transaksi {{ $t->kode }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act act-del" title="Hapus">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="empty">Belum ada transaksi pada periode ini.</td></tr>
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
            <div><dt>Tanggal</dt><dd id="dTanggal"></dd></div>
            <div><dt>Kasir</dt><dd id="dKasir"></dd></div>
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

{{-- Modal edit (owner) --}}
@if ($isOwner)
<div class="modal" id="editModal">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-head">
            <h3 id="eTitle">Edit Transaksi</h3>
            <button type="button" class="modal-close" data-close>&times;</button>
        </div>

        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-grid" style="grid-template-columns:1fr">
                <div class="fg">
                    <label for="e_metode">Metode Pembayaran</label>
                    <select id="e_metode" name="metode">
                        @foreach ($metodeList as $m) <option value="{{ $m }}">{{ $m }}</option> @endforeach
                    </select>
                </div>
                <div class="fg">
                    <label for="e_status">Status</label>
                    <select id="e_status" name="status">
                        @foreach ($statusList as $val => $label) <option value="{{ $val }}">{{ $label }}</option> @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" data-close>Batal</button>
                <button type="submit" class="btn-add">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // ===== Grafik penjualan =====
    const labels = @json($grafik['labels']);
    const data   = @json($grafik['data']);

    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(208, 102, 138, .30)');
    gradient.addColorStop(1, 'rgba(208, 102, 138, 0)');

    function formatRupiah(v) {
        if (v === 0) return '0';
        if (v >= 1000000) return (v / 1000000).toFixed(1).replace('.', ',') + 'Jt';
        return (v / 1000) + 'Rb';
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                borderColor: '#c2456e',
                backgroundColor: gradient,
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#c2456e',
                pointRadius: labels.length > 15 ? 0 : 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: formatRupiah, color: '#6b7280', font: { family: 'Poppins', size: 12 } },
                    grid: { color: '#f3e4e9' },
                    border: { display: false },
                },
                x: {
                    ticks: { color: '#6b7280', font: { family: 'Poppins', size: 12 }, maxTicksLimit: 10 },
                    grid: { display: false },
                }
            }
        }
    });

    // ===== Donat metode pembayaran =====
    const metode = @json($metode);
    const kosong = metode.every(m => m.jumlah === 0);

    new Chart(document.getElementById('payChart'), {
        type: 'doughnut',
        data: {
            labels: kosong ? ['Belum ada data'] : metode.map(m => m.nama),
            datasets: [{
                data: kosong ? [1] : metode.map(m => m.jumlah),
                backgroundColor: kosong ? ['#f3e4e9'] : metode.map(m => m.warna),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            cutout: '68%',
            plugins: { legend: { display: false }, tooltip: { enabled: !kosong } }
        }
    });

    // ===== Modal =====
    const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const closeAll = () => document.querySelectorAll('.modal').forEach(m => m.classList.remove('show'));

    document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', closeAll));
    document.querySelectorAll('.modal').forEach(m => m.addEventListener('click', e => { if (e.target === m) closeAll(); }));

    // Detail transaksi
    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = JSON.parse(btn.dataset.detail);

            document.getElementById('dKode').textContent    = d.kode;
            document.getElementById('dTanggal').textContent = d.tanggal;
            document.getElementById('dKasir').textContent   = d.kasir;
            document.getElementById('dMetode').textContent  = d.metode;
            document.getElementById('dStatus').textContent  = d.status;
            document.getElementById('dTotal').textContent   = rp(d.total);

            const tbody = document.getElementById('dItems');
            tbody.innerHTML = '';
            d.items.forEach(i => {
                const tr = document.createElement('tr');
                [i.nama, rp(i.harga), i.jumlah, rp(i.subtotal)].forEach(v => {
                    const td = document.createElement('td');
                    td.textContent = v;            // textContent = aman dari XSS
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });

            document.getElementById('detailModal').classList.add('show');
        });
    });

    // Edit transaksi (owner)
    const editModal = document.getElementById('editModal');
    if (editModal) {
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('editForm').action = btn.dataset.url;
                document.getElementById('eTitle').textContent = 'Edit ' + btn.dataset.kode;
                document.getElementById('e_metode').value = btn.dataset.metode;
                document.getElementById('e_status').value = btn.dataset.status;
                editModal.classList.add('show');
            });
        });
    }
</script>
@endpush