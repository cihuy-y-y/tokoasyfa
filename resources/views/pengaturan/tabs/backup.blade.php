@php
    $terakhir = ! empty($s['last_backup_at']) ? \Carbon\Carbon::parse($s['last_backup_at'])->format('d/m/Y H:i') : null;
@endphp

<section class="set-card form-card">
    <div class="set-head">
        <span class="set-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></svg></span>
        <div>
            <h2>Backup Data</h2>
            <p>Unduh cadangan seluruh data toko dalam file .sql</p>
        </div>
    </div>

    <div class="backup-box">
        <p><strong>Backup terakhir:</strong> {{ $terakhir ?? 'Belum pernah' }}</p>
        <ul>
            <li>File berisi data semua tabel (pengguna, barang, transaksi, pengaturan).</li>
            <li>Gambar produk, logo, dan banner <strong>tidak</strong> ikut, salin manual folder <code>public/images</code>.</li>
            <li>File memuat hash password pengguna, jadi simpan di tempat yang aman.</li>
        </ul>
        <a href="{{ route('pengaturan.backup') }}" class="btn-add">⬇ Download Backup</a>
    </div>
</section>