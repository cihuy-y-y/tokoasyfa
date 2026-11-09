@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

    // Transaksi lama (dari seeder) belum punya rincian pembayaran, jadi diberi nilai cadangan
    $subtotal = $t->subtotal ?: $t->items->sum('subtotal');
    $diskon   = $t->diskon_nominal ?? 0;
    $bayar    = $t->bayar ?: $t->total;
    $kembali  = $t->kembali ?? 0;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $t->kode }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f3f4f6; font-family: 'Courier New', monospace; font-size: 12px; color: #111; }

        .struk {
            width: 302px;                 /* kertas thermal 80 mm */
            margin: 20px auto;
            padding: 16px 14px 20px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .12);
        }
        .center { text-align: center; }
        .toko { font-size: 16px; font-weight: bold; }
        .kecil { font-size: 11px; color: #444; }
        .garis { border-top: 1px dashed #000; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .item { margin-bottom: 6px; }
        .item .nama { font-weight: bold; word-break: break-word; }
        .total { font-size: 14px; font-weight: bold; }
        .terima { margin-top: 12px; text-align: center; }

        .aksi { width: 302px; margin: 16px auto 0; display: flex; gap: 8px; }
        .aksi button {
            flex: 1; padding: 10px; border: none; border-radius: 8px;
            font-family: sans-serif; font-size: 14px; cursor: pointer;
        }
        .btn-p { background: #d0668a; color: #fff; }
        .btn-t { background: #fff; border: 1px solid #d1d5db !important; }

        @page { size: 80mm auto; margin: 0; }
        @media print {
            body { background: #fff; }
            .struk { width: 100%; margin: 0; box-shadow: none; }
            .aksi { display: none; }
        }
    </style>
</head>
<body>

<div class="aksi">
    <button class="btn-p" onclick="window.print()">🖨 Cetak</button>
    <button class="btn-t" onclick="window.close()">Tutup</button>
</div>

<div class="struk">
    <div class="center">
        <div class="toko">{{ $s['nama_toko'] }}</div>
        <div class="kecil">{!! nl2br(e($s['alamat'])) !!}</div>
        <div class="kecil">Telp: {{ $s['telepon'] }}</div>
    </div>

    <div class="garis"></div>

    <div class="row"><span>No</span><span>{{ $t->kode }}</span></div>
    <div class="row"><span>Tanggal</span><span>{{ $t->tanggal->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Kasir</span><span>{{ $t->kasir->name ?? '-' }}</span></div>

    <div class="garis"></div>

    @foreach ($t->items as $i)
        <div class="item">
            <div class="nama">{{ $i->nama_produk }}</div>
            <div class="row">
                <span>{{ $i->jumlah }} x {{ number_format($i->harga, 0, ',', '.') }}</span>
                <span>{{ number_format($i->subtotal, 0, ',', '.') }}</span>
            </div>
        </div>
    @endforeach

    <div class="garis"></div>

    <div class="row"><span>Subtotal</span><span>{{ $rp($subtotal) }}</span></div>
    @if ($diskon > 0)
        <div class="row"><span>Diskon ({{ $t->diskon_persen }}%)</span><span>-{{ $rp($diskon) }}</span></div>
    @endif
    <div class="row total"><span>TOTAL</span><span>{{ $rp($t->total) }}</span></div>

    <div class="garis"></div>

    <div class="row"><span>Metode</span><span>{{ $t->metode }}</span></div>
    @if ($t->metode === 'Tunai')
        <div class="row"><span>Tunai</span><span>{{ $rp($bayar) }}</span></div>
        <div class="row"><span>Kembali</span><span>{{ $rp($kembali) }}</span></div>
    @endif
    <div class="row"><span>Status</span><span>{{ strtoupper($t->status) }}</span></div>

    <div class="garis"></div>

    <div class="terima">
        <div>Terima kasih sudah berbelanja!</div>
        <div class="kecil">Cantik Setiap Hari ♥</div>
        <div class="kecil">Barang yang sudah dibeli tidak dapat ditukar.</div>
    </div>
</div>

@if ($cetak)
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
@endif
</body>
</html>