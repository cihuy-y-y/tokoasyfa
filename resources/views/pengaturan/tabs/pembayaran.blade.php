@php
    $metode = [
        'metode_qris'     => 'QRIS',
        'metode_transfer' => 'Transfer Bank',
        'metode_ewallet'  => 'E-Wallet',
        'metode_cod'      => 'COD (Bayar di Tempat)',
        'metode_tunai'    => 'Tunai',
    ];
@endphp

<form action="{{ route('pengaturan.pembayaran') }}" method="POST">
    @csrf @method('PUT')

    <div class="set-grid">
        <section class="set-card">
            <div class="set-head">
                <span class="set-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/></svg></span>
                <div>
                    <h2>Metode Pembayaran Aktif</h2>
                    <p>Metode yang boleh dipakai saat transaksi</p>
                </div>
            </div>

            <div class="pay-list">
                @foreach ($metode as $k => $label)
                    <div class="pref-item">
                        <span>{{ $label }}</span>
                        <label class="switch">
                            <input type="checkbox" name="{{ $k }}" value="1" @checked(($s[$k] ?? '0') === '1')>
                            <span></span>
                        </label>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="set-card">
            <div class="set-head">
                <span class="set-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10 12 4l9 6M5 10v8M9 10v8M15 10v8M19 10v8M3 20h18"/></svg></span>
                <div>
                    <h2>Rekening &amp; E-Wallet</h2>
                    <p>Tujuan pembayaran untuk transfer dan e-wallet</p>
                </div>
            </div>

            <div class="form-grid one">
                <div class="fg">
                    <label for="b_nama">Nama Bank</label>
                    <input type="text" id="b_nama" name="bank_nama" value="{{ old('bank_nama', $s['bank_nama']) }}" placeholder="Contoh: BRI">
                </div>
                <div class="fg">
                    <label for="b_rek">No. Rekening</label>
                    <input type="text" id="b_rek" name="bank_rekening" value="{{ old('bank_rekening', $s['bank_rekening']) }}">
                </div>
                <div class="fg">
                    <label for="b_an">Atas Nama</label>
                    <input type="text" id="b_an" name="bank_atas_nama" value="{{ old('bank_atas_nama', $s['bank_atas_nama']) }}">
                </div>
                <div class="fg">
                    <label for="b_ew">Nomor E-Wallet (DANA/OVO/GoPay)</label>
                    <input type="text" id="b_ew" name="ewallet_nomor" value="{{ old('ewallet_nomor', $s['ewallet_nomor']) }}">
                </div>
            </div>

            <div class="modal-foot left">
                <button type="submit" class="btn-add">Simpan Pengaturan</button>
            </div>
        </section>
    </div>
</form>