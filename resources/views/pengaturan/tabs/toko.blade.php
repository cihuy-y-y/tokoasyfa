@php
    $logo       = $s['logo'] ?? null;
    $banner     = $s['banner'] ?? null;
    $logoAda    = $logo && file_exists(public_path('images/toko/' . $logo));
    $bannerAda  = $banner && file_exists(public_path('images/toko/' . $banner));
    $modalToko  = $errors->any() && old('_form') === 'toko';
    $aktif      = fn ($k) => ($s[$k] ?? '0') === '1';
@endphp

<div class="set-grid">

    {{-- ============ KIRI ============ --}}
    <div>
        <section class="set-card">
            <div class="set-head">
                <span class="set-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9 5.5 4h13L20 9M4 9v11h16V9M4 9a2.7 2.7 0 0 0 5.3 0 2.7 2.7 0 0 0 5.4 0A2.7 2.7 0 0 0 20 9M9 20v-6h6v6"/></svg></span>
                <div>
                    <h2>Informasi Toko</h2>
                    <p>Data dasar tentang toko kosmetik Asya</p>
                </div>
                <button type="button" class="btn-ubah" id="btnUbahToko">✎ Ubah</button>
            </div>

            <div class="info-body">
                <div class="info-logo">
                    @if ($logoAda)
                        <img src="{{ asset('images/toko/' . $logo) }}" alt="Logo">
                    @else
                        <span class="brand-top">Toko Kosmetik</span>
                        <span class="brand-name">Asyfa</span>
                    @endif
                </div>

                <dl class="info-list">
                    <div><dt>Nama Toko</dt><dd>{{ $s['nama_toko'] }}</dd></div>
                    <div><dt>Alamat</dt><dd>{!! nl2br(e($s['alamat'])) !!}</dd></div>
                    <div><dt>No. Telepon</dt><dd>{{ $s['telepon'] }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $s['email'] }}</dd></div>
                    <div><dt>Jam Operasional</dt><dd>{{ $s['jam_operasional'] }}</dd></div>
                </dl>
            </div>

            <div class="about">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8zM14 3v5h5M9 13h6M9 17h6"/></svg>
                    Tentang Toko
                </h3>
                <div class="about-box">{{ $s['tentang'] ?: '-' }}</div>
            </div>
        </section>

        <div class="notice">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6zM9 12l2 2 4-4"/></svg>
            Perubahan pengaturan akan langsung tersimpan secara otomatis.
        </div>
    </div>

    {{-- ============ KANAN ============ --}}
    <div>   
        {{-- Notifikasi --}}
        <section class="set-card">
            <div class="set-head">
                <span class="set-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg></span>
                <div>
                    <h2>Pengaturan Notifikasi</h2>
                    <p>Atur notifikasi sistem dan pemberitahuan penting</p>
                </div>
            </div>

            <div class="pref-row">
                @foreach (['notif_transaksi' => 'Notifikasi transaksi baru', 'notif_stok' => 'Notifikasi stok menipis', 'notif_laporan' => 'Notifikasi laporan harian'] as $k => $label)
                    <div class="pref-item">
                        <span>{{ $label }}</span>
                        <label class="switch">
                            <input type="checkbox" class="pref-toggle" data-key="{{ $k }}" @checked($aktif($k))>
                            <span></span>
                        </label>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Tampilan --}}
        <section class="set-card">
            <div class="set-head">
                <span class="set-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a9 9 0 1 0 0 18c1.1 0 2-.9 2-2 0-.6-.2-1-.6-1.4-.4-.4-.6-.9-.6-1.4 0-1.1.9-2 2-2h2.3A3.9 3.9 0 0 0 21 10.3C21 6.3 17 3 12 3z"/><circle cx="7.5" cy="11" r="1"/><circle cx="10.5" cy="7" r="1"/><circle cx="15.5" cy="7.5" r="1"/></svg></span>
                <div>
                    <h2>Pengaturan Tampilan</h2>
                    <p>Sesuaikan tema dan tampilan website sesuai kebutuhan</p>
                </div>
            </div>

            <div class="tema-row">
                <div>
                    <h4>Tema Warna</h4>
                    <div class="swatches">
                        @foreach (\App\Models\Setting::TEMA as $key => $t)
                            <button type="button" class="swatch {{ ($s['tema_warna'] ?? 'pink') === $key ? 'active' : '' }}"
                                    data-key="tema_warna" data-value="{{ $key }}" title="{{ $t['label'] }}"
                                    style="background: {{ $t['main'] }}; color: {{ $t['main'] }}"></button>
                        @endforeach
                    </div>
                </div>

                <div>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- Modal ubah informasi toko --}}
<div class="modal {{ $modalToko ? 'show' : '' }}" id="tokoModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Ubah Informasi Toko</h3>
            <button type="button" class="modal-close" data-close>&times;</button>
        </div>

        <form action="{{ route('pengaturan.toko') }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="_form" value="toko">

            @if ($modalToko)
                <div class="alert alert-error">
                    <ul>@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                </div>
            @endif

            <div class="form-grid">
                <div class="fg full">
                    <label for="t_nama">Nama Toko</label>
                    <input type="text" id="t_nama" name="nama_toko" value="{{ old('nama_toko', $s['nama_toko']) }}">
                </div>
                <div class="fg full">
                    <label for="t_alamat">Alamat</label>
                    <textarea id="t_alamat" name="alamat" rows="2">{{ old('alamat', $s['alamat']) }}</textarea>
                </div>
                <div class="fg">
                    <label for="t_telp">No. Telepon</label>
                    <input type="text" id="t_telp" name="telepon" value="{{ old('telepon', $s['telepon']) }}">
                </div>
                <div class="fg">
                    <label for="t_email">Email</label>
                    <input type="email" id="t_email" name="email" value="{{ old('email', $s['email']) }}">
                </div>
                <div class="fg full">
                    <label for="t_jam">Jam Operasional</label>
                    <input type="text" id="t_jam" name="jam_operasional" value="{{ old('jam_operasional', $s['jam_operasional']) }}">
                </div>
                <div class="fg full">
                    <label for="t_tentang">Tentang Toko</label>
                    <textarea id="t_tentang" name="tentang" rows="4">{{ old('tentang', $s['tentang']) }}</textarea>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn-cancel" data-close>Batal</button>
                <button type="submit" class="btn-add">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // ===== Modal =====
    const tokoModal = document.getElementById('tokoModal');
    document.getElementById('btnUbahToko').addEventListener('click', () => tokoModal.classList.add('show'));
    tokoModal.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => tokoModal.classList.remove('show')));
    tokoModal.addEventListener('click', e => { if (e.target === tokoModal) tokoModal.classList.remove('show'); });

    // ===== Simpan otomatis =====
    const prefUrl = @json(route('pengaturan.preferensi'));
    const csrf    = @json(csrf_token());

    function toast(teks, galat = false) {
        const el = document.createElement('div');
        el.className = 'toast' + (galat ? ' error' : '');
        el.textContent = teks;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 1800);
    }

    async function simpanPref(key, value) {
        const res = await fetch(prefUrl, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ key, value: String(value) }),
        });
        if (!res.ok) throw new Error('gagal');
    }

    // Toggle notifikasi
    document.querySelectorAll('.pref-toggle').forEach(el => {
        el.addEventListener('change', async () => {
            try {
                await simpanPref(el.dataset.key, el.checked ? '1' : '0');
                toast('Tersimpan');
            } catch (e) {
                el.checked = !el.checked;
                toast('Gagal menyimpan', true);
            }
        });
    });

    // Tema warna & mode tampilan (halaman dimuat ulang supaya langsung berlaku)
    document.querySelectorAll('.swatch, .mode-btn').forEach(el => {
        el.addEventListener('click', async () => {
            try {
                await simpanPref(el.dataset.key, el.dataset.value);
                location.reload();
            } catch (e) {
                toast('Gagal menyimpan', true);
            }
        });
    });
</script>
@endpush