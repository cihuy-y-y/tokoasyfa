@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')

{{-- Sambutan --}}
<div class="welcome welcome-flex">
    <div>
        <h1>Selamat datang, {{ auth()->user()->name }}!</h1>
        <p>Semoga hari ini penuh dengan kesuksesan <span class="heart">♥</span></p>
    </div>
    <p class="hero-quote">Belanja cantik<br>jadi lebih mudah <span>♥</span></p>
</div>
    
{{-- Kartu statistik --}}
<div class="stat-grid">

    <div class="stat-card">
        <div class="stat-icon ic-pink">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>
            </svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Total Produk</span>
            <strong class="stat-value">{{ number_format($stats['total_produk'], 0, ',', '.') }}</strong>
            <span class="stat-note up">▲ +{{ $stats['produk_baru'] }} produk dari bulan lalu</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/>
            </svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Stok Menipis</span>
            <strong class="stat-value">{{ $stats['stok_menipis'] }}</strong>
            @if ($stats['stok_menipis'] > 0)
                <span class="stat-note warn"><i class="dot-orange"></i> Perlu segera restock</span>
            @else
                <span class="stat-note up">Semua stok aman</span>
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/>
            </svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Transaksi Hari Ini</span>
            <strong class="stat-value">{{ $stats['transaksi_hari_ini'] }}</strong>
            <span class="stat-note {{ $stats['transaksi_selisih'] >= 0 ? 'up' : 'down' }}">
                {{ $stats['transaksi_selisih'] >= 0 ? '▲ +' : '▼ ' }}{{ $stats['transaksi_selisih'] }} dari kemarin
            </span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="5"/><path d="M8 17V8h4a2.5 2.5 0 0 1 0 5H8M12 13l3 4"/>
            </svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Total Penjualan (Hari Ini)</span>
            <strong class="stat-value">Rp {{ number_format($stats['penjualan_hari_ini'], 0, ',', '.') }}</strong>
            @if ($stats['penjualan_persen'] === null)
            <span class="stat-note">Belum ada data kemarin</span>
            @else
            <span class="stat-note {{ $stats['penjualan_persen'] >= 0 ? 'up' : 'down' }}">
                {{ $stats['penjualan_persen'] >= 0 ? '▲ +' : '▼ ' }}{{ $stats['penjualan_persen'] }}% dari kemarin
            </span>
            @endif
        </div>
    </div>

</div>

{{-- Grafik + produk terlaris --}}
<div class="dash-grid">

    <section class="panel">
        <div class="panel-head">
            <h2>{{ $grafik['judul'] }}</h2>
            <form method="GET" action="{{ route('admin.dashboard') }}">
                <select name="periode" class="range-select" onchange="this.form.submit()">
                    <option value="1"  @selected($grafik['periode'] === '1')>1 Hari</option>
                    <option value="7"  @selected($grafik['periode'] === '7')>7 Hari Terakhir</option>
                    <option value="30" @selected($grafik['periode'] === '30')>30 Hari Terakhir</option>
                </select>
            </form>
        </div>
        <div class="chart-wrap">
            <canvas id="salesChart"></canvas>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Produk Terlaris</h2>
        </div>
        <ul class="top-list">
            @forelse ($produkTerlaris as $i => $p)
                <li>
                    <span class="rank">{{ $i + 1 }}</span>

                    <span class="thumb" style="background: {{ $p['warna'] }}">
                        @if (!empty($p['gambar']) && file_exists(public_path('images/produk/' . $p['gambar'])))
                            <img src="{{ asset('images/produk/' . $p['gambar']) }}" alt="{{ $p['nama'] }}">
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>
                            </svg>
                        @endif
                    </span>

                    <span class="top-name">{{ $p['nama'] }}</span>
                    <span class="top-qty">{{ $p['terjual'] }}</span>
                </li>
                @empty
                 <li class="empty-li">Belum ada penjualan 7 hari terakhir.</li>
            @endforelse
        </ul>
    </section>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels = @json($grafik['labels']);
    const data   = @json($grafik['data']);

    const ctx = document.getElementById('salesChart').getContext('2d');

    // Warna isi di bawah garis (gradasi pink)
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(208, 102, 138, .30)');
    gradient.addColorStop(1, 'rgba(208, 102, 138, 0)');

    // Format sumbu Y: 500Rb, 1.0Jt, dst.
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
                tooltip: {
                    callbacks: {
                        label: (c) => 'Rp ' + c.parsed.y.toLocaleString('id-ID')
                    }
                }
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
                    ticks: { color: '#6b7280', font: { family: 'Poppins', size: 12 }, maxTicksLimit: 10 },
                    grid: { display: '#f8eaee' },
                }
            }
        }
    });
</script>
@endpush