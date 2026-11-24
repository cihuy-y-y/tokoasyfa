@extends('layouts.app')

@section('title', 'Transaksi')

@push('styles')
    {{-- dashboard.css = panel, barang.css = judul/modal/tombol, pos.css = khusus halaman ini --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barang.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
@endpush

@section('content')
@php
    $ikonMetode = [
        'QRIS'          => '<path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM20 14v3M14 20h3M20 20h1"/>',
        'E-Wallet'      => '<path d="M3 7a2 2 0 0 1 2-2h12v4M3 7v11a2 2 0 0 0 2 2h14a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1H5a2 2 0 0 1-2-2zM16 14h2"/>',
        'COD'           => '<path d="M1 6h13v10H1zM14 9h4l3 3v4h-7"/><circle cx="5.5" cy="18" r="1.8"/><circle cx="17.5" cy="18" r="1.8"/>',
        'Tunai'         => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/>',
    ];
@endphp

{{-- Judul --}}
<div class="page-bar">
    @if (count($tertunda))
    <div class="pending-bar">
        <strong>Pembayaran belum selesai:</strong>
        @foreach ($tertunda as $t)
            <span>{{ $t['kode'] }} · Rp {{ number_format($t['total'], 0, ',', '.') }} · {{ $t['metode'] }}</span>
            <button type="button" class="btn-lanjut" data-id="{{ $t['id'] }}" data-url="{{ $t['url'] }}"
                    data-total="{{ $t['total'] }}" data-metode="{{ $t['metode'] }}">Tampilkan QR</button>
        @endforeach
    </div>
    @endif
    <div class="page-title">
        <span class="page-ico round">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>
            </svg>
        </span>
        <div>
            <h1>Transaksi Penjualan</h1>
            <p>Lakukan transaksi penjualan dengan cepat dan mudah <span class="heart">♥</span></p>
        </div>
    </div>
    <div class="pos-meta">
        <span class="pos-clock" id="posClock">-</span>
        <span class="pos-kasir" title="{{ auth()->user()->name }}">● Kasir Aktif</span>
    </div>
</div>

<div class="pos-top">

    {{-- ============ DAFTAR PRODUK ============ --}}
    <section class="panel">
        <div class="panel-head">
            <h2 class="h-sm">Daftar Produk</h2>
            <select id="selKategori" class="range-select">
                <option value="">Semua Kategori</option>
                @foreach ($kategori as $k) <option value="{{ $k }}">{{ $k }}</option> @endforeach
            </select>
        </div>

        <div class="pos-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="text" id="cariProduk" placeholder="Cari nama atau kode produk... (Enter untuk kode persis)" autocomplete="off">
        </div>

        <div class="chips" id="chips">
            <button type="button" class="chip active" data-kat="">Semua</button>
            @foreach ($kategori as $k)
                <button type="button" class="chip" data-kat="{{ $k }}">{{ $k }}</button>
            @endforeach
        </div>

        <div class="prod-grid" id="prodGrid"></div>
    </section>

    {{-- ============ KERANJANG ============ --}}
    <section class="panel">
        <div class="panel-head">
            <h2 class="h-sm">🛒 Keranjang Belanja</h2>
            <button type="button" class="link-del" id="btnKosongkan">Hapus Semua 🗑</button>
        </div>

        <div class="cart-wrap">
            <table class="cart-table">
                <thead>
                    <tr><th>No</th><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th></th></tr>
                </thead>
                <tbody id="cartBody"></tbody>
            </table>
        </div>

        <div class="totals">
            <div class="t-row"><span>Subtotal</span><strong id="tSubtotal">Rp 0</strong></div>
            <div class="t-row">
                <span>Diskon</span>
                <span class="disc-box">
                    <input type="number" id="diskon" min="0" max="100" value="0"><i>%</i>
                </span>
                <strong id="tDiskon">Rp 0</strong>
            </div>
            <div class="t-row total"><span>Total</span><strong id="tTotal">Rp 0</strong></div>
        </div>
    </section>
</div>

<div class="pos-bottom">

    {{-- ============ METODE PEMBAYARAN ============ --}}
    <section class="panel">
        <div class="panel-head"><h2 class="h-sm">💳 Metode Pembayaran</h2></div>

        @if (count($metode))
            <div class="method-grid" id="methodGrid">
                @foreach ($metode as $i => $m)
                    <button type="button" class="m-card {{ $i === 0 ? 'active' : '' }}" data-metode="{{ $m }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">{!! $ikonMetode[$m] !!}</svg>
                        <span>{{ $m }}</span>
                    </button>
                @endforeach
            </div>
        @else
            <p class="prod-empty">Belum ada metode pembayaran aktif. Minta owner mengaktifkannya di Pengaturan &gt; Payment Gateway.</p>
        @endif
    </section>

    {{-- ============ AKSI ============ --}}
    <section class="pos-actions">
        <button type="button" class="btn-pay" id="btnProses">💳 Proses Pembayaran</button>
        <button type="button" class="btn-print" id="btnCetak">🖨 Cetak Struk</button>
    </section>
</div>

{{-- ============ MODAL PEMBAYARAN ============ --}}
<div class="modal" id="payModal">
    <div class="modal-box sm">
        <div class="modal-head">
            <h3>Pembayaran</h3>
            <button type="button" class="modal-close" data-close>&times;</button>
        </div>

        <div class="pay-sum">
            <span>Total yang harus dibayar</span>
            <strong id="payTotal">Rp 0</strong>
            <small>Metode: <b id="payMetode">-</b></small>
        </div>

        <p class="pay-info" id="payInfo"></p>

        <div id="tunaiBox">
            <div class="fg">
                <label for="uangDiterima">Uang diterima (Rp)</label>
                <input type="number" id="uangDiterima" min="0" placeholder="Masukkan nominal">
            </div>
            <div class="quick-cash" id="quickCash"></div>
            <div class="change-box">
                <span>Kembalian</span>
                <strong id="kembalian">Rp 0</strong>
            </div>
        </div>

        <div id="onlineBox" style="display:none">
            <div class="qr-wrap"><div id="qrCanvas"></div></div>
            <p class="qr-status" id="qrStatus">Menunggu pembayaran...</p>
            <p class="qr-timer">Kedaluwarsa dalam <b id="qrTimer">15:00</b></p>
            <button type="button" class="btn-cancel qr-cancel" id="btnBatalOnline">Batalkan Pembayaran</button>
        </div>

        <div class="modal-foot" id="payFoot">
            <button type="button" class="btn-cancel" data-close>Batal</button>
            <button type="button" class="btn-add" id="btnKonfirmasi">Konfirmasi &amp; Simpan</button>
        </div>
    </div>
</div>

{{-- ============ MODAL SUKSES ============ --}}
<div class="modal" id="okModal">
    <div class="modal-box sm ok-box">
        <div class="ok-ico">✓</div>
        <h3>Pembayaran Berhasil</h3>
        <p class="ok-kode" id="okKode">-</p>

        <div class="pay-sum compact">
            <div class="t-row"><span>Total</span><strong id="okTotal">Rp 0</strong></div>
            <div class="t-row"><span>Kembalian</span><strong id="okKembali">Rp 0</strong></div>
        </div>

        <div class="modal-foot center">
            <button type="button" class="btn-cancel" id="okBaru">Transaksi Baru</button>
            <button type="button" class="btn-add" id="okCetak">🖨 Cetak Struk</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    // ===== Data dari server =====
    const PRODUK    = @json($produk);
    const INFO      = @json($info);
    const STORE_URL = @json(route('pos.store'));
    const ONLINE     = ['QRIS', 'E-Wallet'];
    const ONLINE_URL = @json(route('pos.online'));
    let poll = null, tick = null;
    const CSRF      = @json(csrf_token());
    const byId      = Object.fromEntries(PRODUK.map(p => [p.id, p]));

    // ===== Helper =====
    const $  = id => document.getElementById(id);
    const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const PLACEHOLDER = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>';

    function toast(teks, galat = false) {
        const el = document.createElement('div');
        el.className = 'toast' + (galat ? ' error' : '');
        el.textContent = teks;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), galat ? 3500 : 1800);
    }

    // ===== State =====
    let cart = {};                       // { idProduk: jumlah }
    let kategori = '';
    let kata = '';
    let metode = (document.querySelector('.m-card.active') || {}).dataset?.metode || null;
    let strukTerakhir = null;

    // ===== Jam =====
    function jam() {
        const d = new Date(), p = n => String(n).padStart(2, '0');
        $('posClock').textContent = `${p(d.getDate())}/${p(d.getMonth() + 1)}/${d.getFullYear()} · ${p(d.getHours())}:${p(d.getMinutes())}`;
    }
    jam(); setInterval(jam, 20000);

    // ===== Daftar produk =====
    function renderProduk() {
        const kt = kata.toLowerCase();
        const list = PRODUK.filter(p =>
            (!kategori || p.kategori === kategori) &&
            (!kt || p.nama.toLowerCase().includes(kt) || (p.kode || '').toLowerCase().includes(kt))
        );

        if (!list.length) {
            $('prodGrid').innerHTML = '<p class="prod-empty">Produk tidak ditemukan.</p>';
            return;
        }

        $('prodGrid').innerHTML = list.map(p => {
            const habis = p.stok <= 0;
            return `<div class="prod-card ${habis ? 'out' : ''}">
                <div class="prod-img">${p.gambar ? `<img src="${esc(p.gambar)}" alt="">` : PLACEHOLDER}</div>
                <div class="prod-name">${esc(p.nama)}</div>
                <div class="prod-price">${rp(p.harga)}</div>
                <small class="prod-stok">Stok: ${p.stok}</small>
                <button type="button" class="btn-tambah" data-id="${p.id}" ${habis ? 'disabled' : ''}>${habis ? 'Habis' : '+ Tambah'}</button>
            </div>`;
        }).join('');
    }

    $('prodGrid').addEventListener('click', e => {
        const btn = e.target.closest('.btn-tambah');
        if (btn) tambah(Number(btn.dataset.id));
    });

    $('selKategori').addEventListener('change', e => setKategori(e.target.value));
    $('chips').addEventListener('click', e => {
        const c = e.target.closest('.chip');
        if (c) setKategori(c.dataset.kat);
    });
    function setKategori(k) {
        kategori = k;
        $('selKategori').value = k;
        document.querySelectorAll('.chip').forEach(c => c.classList.toggle('active', c.dataset.kat === k));
        renderProduk();
    }

    $('cariProduk').addEventListener('input', e => { kata = e.target.value.trim(); renderProduk(); });
    // Enter dengan kode barang persis (cocok untuk scanner barcode)
    $('cariProduk').addEventListener('keydown', e => {
        if (e.key !== 'Enter') return;
        const p = PRODUK.find(x => (x.kode || '').toLowerCase() === e.target.value.trim().toLowerCase());
        if (p) { tambah(p.id); e.target.value = ''; kata = ''; renderProduk(); }
    });

    // ===== Keranjang =====
    function tambah(id) {
        const p = byId[id], sekarang = cart[id] || 0;
        if (sekarang + 1 > p.stok) return toast(`Stok ${p.nama} hanya ${p.stok}`, true);
        cart[id] = sekarang + 1;
        renderCart();
    }

    function ubahQty(id, qty) {
        const p = byId[id];
        if (qty <= 0) { delete cart[id]; }
        else if (qty > p.stok) { toast(`Stok ${p.nama} hanya ${p.stok}`, true); return; }
        else { cart[id] = qty; }
        renderCart();
    }

    function hitung() {
        let subtotal = 0;
        Object.entries(cart).forEach(([id, q]) => subtotal += byId[id].harga * q);

        let persen = parseInt($('diskon').value, 10);
        if (isNaN(persen) || persen < 0) persen = 0;
        if (persen > 100) persen = 100;

        const diskon = Math.floor(subtotal * persen / 100);
        return { subtotal, persen, diskon, total: subtotal - diskon };
    }

    function renderCart() {
        const ids = Object.keys(cart);

        $('cartBody').innerHTML = ids.length
            ? ids.map((id, i) => {
                const p = byId[id], q = cart[id];
                return `<tr>
                    <td>${i + 1}</td>
                    <td><div class="c-prod"><span class="c-thumb">${p.gambar ? `<img src="${esc(p.gambar)}" alt="">` : PLACEHOLDER}</span><span>${esc(p.nama)}</span></div></td>
                    <td>${rp(p.harga)}</td>
                    <td><div class="qty">
                        <button type="button" data-act="min" data-id="${id}">−</button>
                        <span>${q}</span>
                        <button type="button" data-act="plus" data-id="${id}">+</button>
                    </div></td>
                    <td><strong>${rp(p.harga * q)}</strong></td>
                    <td><button type="button" class="c-del" data-act="del" data-id="${id}" title="Hapus">🗑</button></td>
                </tr>`;
            }).join('')
            : '<tr><td colspan="6" class="cart-empty">Keranjang masih kosong. Klik "Tambah" pada produk.</td></tr>';

        const h = hitung();
        $('tSubtotal').textContent = rp(h.subtotal);
        $('tDiskon').textContent   = rp(h.diskon);
        $('tTotal').textContent    = rp(h.total);
    }

    $('cartBody').addEventListener('click', e => {
        const b = e.target.closest('[data-act]');
        if (!b) return;
        const id = Number(b.dataset.id);
        if (b.dataset.act === 'plus') ubahQty(id, (cart[id] || 0) + 1);
        if (b.dataset.act === 'min')  ubahQty(id, (cart[id] || 0) - 1);
        if (b.dataset.act === 'del')  ubahQty(id, 0);
    });

    $('diskon').addEventListener('input', renderCart);

    $('btnKosongkan').addEventListener('click', () => {
        if (Object.keys(cart).length && confirm('Kosongkan keranjang?')) { cart = {}; renderCart(); }
    });

    // ===== Metode pembayaran =====
    const methodGrid = $('methodGrid');
    if (methodGrid) {
        methodGrid.addEventListener('click', e => {
            const c = e.target.closest('.m-card');
            if (!c) return;
            metode = c.dataset.metode;
            methodGrid.querySelectorAll('.m-card').forEach(x => x.classList.toggle('active', x === c));
        });
    }

    // ===== Modal =====
    const closeAll = () => document.querySelectorAll('.modal').forEach(m => m.classList.remove('show'));
    document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', closeAll));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });

    // ===== Jendela pembayaran =====
    let totalBayar = 0;

    function bukaBayar() {
        if (!Object.keys(cart).length) return toast('Keranjang masih kosong', true);
        if (!metode) return toast('Pilih metode pembayaran', true);

        const h = hitung();
        totalBayar = h.total;

        $('payTotal').textContent  = rp(h.total);
        $('payMetode').textContent = metode;

        const info = {
            'QRIS':     'Kode QRIS dibuat sesuai total. Terkonfirmasi otomatis setelah dibayar.',
            'E-Wallet': 'Pelanggan memindai kode dengan GoPay. Terkonfirmasi otomatis.',
        }[metode] || '';
        $('payInfo').textContent   = info;
        $('payInfo').style.display = info ? '' : 'none';

        const tunai = metode === 'Tunai';
        $('tunaiBox').style.display = tunai ? '' : 'none';
        $('uangDiterima').value = '';

        if (tunai) {
            // Tombol nominal cepat: uang pas dan pembulatan ke atas
            const opsi = [...new Set([
                totalBayar,
                Math.ceil(totalBayar / 10000)  * 10000,
                Math.ceil(totalBayar / 50000)  * 50000,
                Math.ceil(totalBayar / 100000) * 100000,
            ])].filter(n => n >= totalBayar && n > 0).slice(0, 4);

            $('quickCash').innerHTML = '';
            opsi.forEach((n, i) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = i === 0 ? 'Uang pas' : rp(n);
                b.addEventListener('click', () => { $('uangDiterima').value = n; hitungKembali(); });
                $('quickCash').appendChild(b);
            });
        }

        $('btnKonfirmasi').textContent = ONLINE.includes(metode) ? 'Buat Kode Pembayaran' : 'Konfirmasi & Simpan';
        $('onlineBox').style.display = 'none';
        $('payFoot').style.display   = ''; 
           
        hitungKembali();
        $('payModal').classList.add('show');
        if (tunai) $('uangDiterima').focus();
    }

    function hitungKembali() {
        if (metode !== 'Tunai') { $('btnKonfirmasi').disabled = false; return; }
        const bayar = parseInt($('uangDiterima').value, 10) || 0;
        $('kembalian').textContent = rp(Math.max(0, bayar - totalBayar));
        $('btnKonfirmasi').disabled = bayar < totalBayar;
    }
    $('uangDiterima').addEventListener('input', hitungKembali);

    $('btnProses').addEventListener('click', bukaBayar);

    // ===== Simpan transaksi =====
    function stopOnline() { clearInterval(poll); clearInterval(tick); poll = tick = null; }

    function selesai(json) {
        stopOnline();
        Object.entries(cart).forEach(([id, q]) => byId[id].stok -= q);
        cart = {};
        $('diskon').value = 0;
        strukTerakhir = json.struk_url;

        $('okKode').textContent    = json.kode;
        $('okTotal').textContent   = rp(json.total);
        $('okKembali').textContent = rp(json.kembali);

        closeAll();
        $('okModal').classList.add('show');
        renderProduk();
        renderCart();
    }

    function mulaiOnline(json) {
        $('tunaiBox').style.display  = 'none';
        $('payInfo').style.display   = 'none';
        $('payFoot').style.display   = 'none';
        $('onlineBox').style.display = '';
        $('qrStatus').textContent    = 'Menunggu pembayaran...';
        console.log('QR URL:', json.qr_url);

        $('qrCanvas').innerHTML = '';
        if (json.qr_string) {
            new QRCode($('qrCanvas'), { text: json.qr_string, width: 220, height: 220 });
        } else if (json.qr_url) {
            $('qrCanvas').innerHTML = `<img src="${esc(json.qr_url)}" width="220" height="220" alt="QR">`;
        }

        let sisa = json.sisa_detik;
        const tampil = () => {
            const m = String(Math.floor(sisa / 60)).padStart(2, '0'), s = String(sisa % 60).padStart(2, '0');
            $('qrTimer').textContent = `${m}:${s}`;
        };
        tampil();
        tick = setInterval(() => { sisa = Math.max(0, sisa - 1); tampil(); }, 1000);

        poll = setInterval(async () => {
            try {
                const r = await fetch(json.status_url, { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                if (d.status === 'lunas') {
                    selesai(d);
                } else if (d.status === 'batal') {
                    stopOnline(); closeAll();
                    toast('Pembayaran dibatalkan atau kedaluwarsa', true);
                }
            } catch (e) { /* coba lagi pada putaran berikutnya */ }
        }, 3000);

        $('btnBatalOnline').onclick = async () => {
            stopOnline();
            await fetch(json.batal_url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
            closeAll();
            toast('Pembayaran dibatalkan');
        };
    }

    $('btnKonfirmasi').addEventListener('click', async () => {
        const btn = $('btnKonfirmasi'), h = hitung(), online = ONLINE.includes(metode);
        const label = online ? 'Buat Kode Pembayaran' : 'Konfirmasi & Simpan';
        btn.disabled = true;
        btn.textContent = 'Memproses...';

        try {
            const res = await fetch(online ? ONLINE_URL : STORE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    items:  Object.entries(cart).map(([id, qty]) => ({ id: Number(id), qty })),
                    diskon: h.persen,
                    metode: metode,
                    bayar:  metode === 'Tunai' ? (parseInt($('uangDiterima').value, 10) || 0) : null,
                }),
            });
            const json = await res.json().catch(() => ({}));

            if (!res.ok) {
                toast(json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal memproses transaksi.'), true);
                return;
            }

            online ? mulaiOnline(json) : selesai(json);
        } catch (err) {
            toast('Koneksi bermasalah. Coba lagi.', true);
        } finally {
            btn.textContent = label;
            hitungKembali();
        }
    });

    // ===== Struk =====
    function cetakStruk() {
        if (!strukTerakhir) return toast('Belum ada transaksi untuk dicetak', true);
        window.open(strukTerakhir + '?cetak=1', '_blank');
    }
    $('btnCetak').addEventListener('click', cetakStruk);
    $('okCetak').addEventListener('click', cetakStruk);
    $('okBaru').addEventListener('click', closeAll);

    async function lanjutkan(b) {
    stopOnline();
    const res  = await fetch(b.dataset.url, { headers: { 'Accept': 'application/json' } });
    const json = await res.json().catch(() => ({}));
    if (!res.ok) { toast(json.message || 'Gagal membuka pembayaran.', true); setTimeout(() => location.reload(), 1500); return; }

    metode = b.dataset.metode;
    $('payTotal').textContent  = rp(b.dataset.total);
    $('payMetode').textContent = metode;
    $('payModal').classList.add('show');
    mulaiOnline(json);
}
document.querySelectorAll('.btn-lanjut').forEach(b => b.addEventListener('click', () => lanjutkan(b)));

// Dibuka dari Laporan (?lanjut=ID)
const autoId = @json(request()->query('lanjut'));

if (autoId) {
    document.querySelector(
        `.btn-lanjut[data-id="${autoId}"]`
    )?.click();
}

    // ===== Mulai =====
    renderProduk();
    renderCart();
</script>
@endpush