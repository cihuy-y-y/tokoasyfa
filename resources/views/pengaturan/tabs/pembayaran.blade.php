@php
    $metode = [
        'metode_qris'     => 'QRIS',
        'metode_ewallet'  => 'E-Wallet',
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
            <div class="modal-foot left">
                <button type="submit" class="btn-add">Simpan Pengaturan</button>
            </div>
            </div>
        </section>
    </div>
</form>