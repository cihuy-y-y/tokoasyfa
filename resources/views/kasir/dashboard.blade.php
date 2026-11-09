@extends('layouts.app')

@section('title', 'Dashboard Kasir')

@push('styles')
    {{-- dashboard.css = kartu & panel, barang.css = tabel & badge, kasir.css = khusus halaman ini --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barang.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kasir.css') }}">
@endpush

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $rp   = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $link = fn ($nama) => Route::has($nama) ? route($nama) : '#';

    $statusLabel = ['lunas' => 'Berhasil', 'pending' => 'Pending', 'batal' => 'Batal'];

    // Pil tren: persen (null = belum ada pembanding)
    $pil = function ($nilai, $suffix = '%') {
        if ($nilai === null) return ['', '-'];
        $naik = $nilai >= 0;
        return [$naik ? 'up' : 'down', ($naik ? '↑ +' : '↓ ') . $nilai . $suffix];
    };

    $cards = [
        ['k1', 'ic-pink',   'Total Penjualan Hari Ini', $rp($now['total']),                          $pil($tren['total']),
         '<path d="M6 7h12l1 13H5zM9 7a3 3 0 0 1 6 0"/>'],
        ['k2', 'ic-purple', 'Jumlah Transaksi',         $now['jumlah'] . ' Transaksi',                $pil($tren['jumlah'], ''),
         '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8zM14 3v5h5M9 13h6M9 17h6"/>'],
        ['k3', 'ic-green',  'Produk Terjual',           number_format($now['pcs'], 0, ',', '.') . ' pcs', $pil($tren['pcs']),
         '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>'],
        ['k4', 'ic-orange', 'Rata-rata Belanja',        $rp($now['rata']),                           $pil($tren['rata']),
         '<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>'],
    ];

    $akses = [
        ['Transaksi Baru', 'pos.index',      '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>'],
        ['Cek Stok',       'kasir.stok',     '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>'],
        ['Laporan',        'kasir.laporan',  '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8zM14 3v5h5M9 13h6M9 17h6"/>'],
    ];
@endphp

{{-- Judul --}}
<div class="hero-bar">
    <div class="page-title">
        <span class="page-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>
            </svg>
        </span>
        <div>
            <h1>Dashboard Kasir</h1>
            <p>Selamat datang, {{ auth()->user()->name }}! Silakan mulai melayani pelanggan hari ini! <span class="heart">♥</span></p>
        </div>
    </div>
    <p class="hero-quote">Belanja cantik<br>jadi lebih mudah <span>♥</span></p>
</div>

{{-- Kartu ringkasan --}}
<div class="stat-grid">
    @foreach ($cards as [$kelas, $warna, $label, $nilai, [$arah, $teks], $ikon])
        <div class="stat-card {{ $kelas }}">
            <div class="stat-icon {{ $warna }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $ikon !!}</svg>
            </div>
            <div class="stat-body">
                <span class="stat-label">{{ $label }}</span>
                <strong class="stat-value">{{ $nilai }}</strong>
            </div>
            <span class="trend {{ $arah }}">{{ $teks }}</span>
        </div>
    @endforeach
</div>

{{-- Grafik + transaksi terbaru --}}
<div class="k-grid">
    <section class="panel">
        <div class="panel-head">
            <h2 class="h-sm">Grafik Penjualan</h2>
            <span class="period-badge">7 Hari Terakhir</span>
        </div>
        <div class="chart-wrap"><canvas id="salesChart"></canvas></div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2 class="h-sm">Transaksi Terbaru</h2>
            <a href="{{ $link('kasir.laporan') }}" class="see-all">Lihat Semua →</a>
        </div>

        <div class="table-wrap">
            <table class="data-table mini-table">
                <thead>
                    <tr><th>No</th><th>Tanggal</th><th>Total</th><th>Metode</th><th>Status</th></tr>
                </thead>
                <tbody>
                @forelse ($terbaru as $t)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $t->kode }}</strong>
                            <small class="p-sku">{{ $t->tanggal->format('d/m/Y · H:i') }}</small>
                        </td>
                        <td>{{ $rp($t->total) }}</td>
                        <td>{{ $t->metode }}</td>
                        <td><span class="st st-{{ $t->status }}">{{ $statusLabel[$t->status] ?? $t->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada transaksi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- Tips + akses cepat --}}
<div class="k-grid">
    <section class="tips">
        <span class="tips-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1zM15 9a4 4 0 0 1 0 6M18 6a8 8 0 0 1 0 12"/></svg>
        </span>
        <div>
            <h3>Tips Hari Ini</h3>
            <p>{{ $tip }} <span class="heart">♥</span></p>
        </div>
        <span class="tips-gift">🎁</span>
    </section>

    <section class="panel">
        <div class="panel-head"><h2 class="h-sm">⚡ Akses Cepat</h2></div>
        <div class="quick-grid">
            @foreach ($akses as [$nama, $route, $ikon])
                <a href="{{ $link($route) }}" class="quick">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $ikon !!}</svg>
                    <span>{{ $nama }}</span>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels = @json($grafik['labels']);
    const data   = @json($grafik['data']);

    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(236, 72, 140, .28)');
    gradient.addColorStop(1, 'rgba(236, 72, 140, 0)');

    function formatRupiah(v) {
        if (v === 0) return '0';
        if (v >= 1000000) return (v / 1000000).toFixed(1).replace('.', ',') + 'Jt';
        if (v >= 1000)    return Math.round(v / 1000) + 'Rb';
        return '';   // nilai di bawah 1.000 tidak ditampilkan
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                borderColor: '#ec4899',
                backgroundColor: gradient,
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ec4899',
                pointRadius: 4,
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
                    suggestedMax: Math.max(...data) === 0 ? 100000 : undefined,
                    ticks: { callback: formatRupiah, color: '#6b7280', font: { family: 'Poppins', size: 12 } },
                    grid: { color: '#f3e4e9' },
                    border: { display: false },
                },
                x: {
                    ticks: { color: '#374151', font: { family: 'Poppins', size: 12 } },
                    grid: { color: '#f8eaee' },
                }
            }
        }
    });
</script>
@endpush