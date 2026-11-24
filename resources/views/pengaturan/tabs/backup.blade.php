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
        <a href="{{ route('pengaturan.backup') }}" data-turbo="false" class="btn-add">⬇ Download Backup</a>
    </div>
</section>
<section class="set-card form-card">
    <div class="set-head">
        <span class="set-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M4 19h16"/></svg></span>
        <div>
            <h2>Impor Backup</h2>
            <p>Pulihkan data dari file .sql hasil Download Backup</p>
        </div>
    </div>

    <div class="impor-warn">
        <strong>Perhatian:</strong>
        <ul>
            <li>Data pada tabel yang ada di file (pengguna, barang, transaksi, pengaturan) akan <b>diganti</b> dengan isi file.</li>
            <li>Sistem otomatis menyimpan cadangan kondisi sekarang di <code>storage/app/backups</code> sebelum mengganti.</li>
            <li>Setelah berhasil, Anda <b>logout otomatis</b> dan harus login dengan akun dari file backup.</li>
            <li>Gambar produk, logo, dan banner tidak ikut. Salin manual folder <code>public/images</code>.</li>
        </ul>
    </div>

    <form action="{{ route('pengaturan.impor') }}" method="POST" enctype="multipart/form-data" data-turbo="false"
          onsubmit="return confirm('Data saat ini akan diganti dengan isi file backup. Lanjutkan?')">
        @csrf

        <div class="form-grid one">
            <div class="fg">
                <label for="impor_file">File Backup (.sql, maks 20 MB)</label>
                <input type="file" id="impor_file" name="file" accept=".sql,.txt" required>
            </div>
            <div class="fg">
                <label for="impor_konf">Ketik <b>IMPOR</b> untuk konfirmasi</label>
                <input type="text" id="impor_konf" name="konfirmasi" autocomplete="off" placeholder="IMPOR" required>
            </div>
        </div>

        <div class="modal-foot left">
            <button type="submit" class="btn-add">⬆️ Impor Sekarang</button>
        </div>
    </form>
</section>
