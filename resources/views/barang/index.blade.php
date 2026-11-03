@extends('layouts.app')

@section('title', 'Data Barang')

@push('styles')
    {{-- dashboard.css dipakai untuk gaya kartu ringkasan (.stat-card, dst.) --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barang.css') }}">
@endpush

@section('content')

{{-- Judul halaman --}}
<div class="page-bar">
    <div class="page-title">
        <span class="page-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>
            </svg>
        </span>
        <div>
            <h1>Data Barang</h1>
            <p>Kelola data produk dan pantau stok barang di toko Anda</p>
        </div>
    </div>
    <button type="button" class="btn-add" id="btnAdd">+ Tambah Barang</button>
</div>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if (session('error'))   <div class="alert alert-error">{{ session('error') }}</div>   @endif
@error('ids')            <div class="alert alert-error">{{ $message }}</div>            @enderror

{{-- Ringkasan stok --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon ic-pink">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>
            </svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Total Produk</span>
            <strong class="stat-value">{{ number_format($summary['total'], 0, ',', '.') }}</strong>
            <span class="stat-note">Semua produk yang tersedia</span>
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
            <strong class="stat-value">{{ number_format($summary['menipis'], 0, ',', '.') }}</strong>
            <span class="stat-note">Perlu segera ditambah</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6 9 17l-5-5"/>
            </svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Stok Aman</span>
            <strong class="stat-value">{{ number_format($summary['aman'], 0, ',', '.') }}</strong>
            <span class="stat-note">Stok dalam kondisi baik</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>
            </svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Total Stok</span>
            <strong class="stat-value">{{ number_format($summary['total_stok'], 0, ',', '.') }}</strong>
            <span class="stat-note">Jumlah seluruh barang</span>
        </div>
    </div>
</div>

{{-- Tabel --}}
<section class="panel">

    {{-- Filter --}}
    <form method="GET" action="{{ route('barang.index') }}" class="filter-bar">
        <div class="f-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
            </svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama produk atau kode barang...">
        </div>

        <select name="kategori" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach ($kategoriList as $k)
                <option value="{{ $k }}" @selected(request('kategori') === $k)>{{ $k }}</option>
            @endforeach
        </select>

        <select name="stok" onchange="this.form.submit()">
            <option value="">Semua Status Stok</option>
            <option value="tersedia" @selected(request('stok') === 'tersedia')>Tersedia</option>
            <option value="menipis"  @selected(request('stok') === 'menipis')>Stok Menipis</option>
            <option value="habis"    @selected(request('stok') === 'habis')>Habis</option>
        </select>

        <a href="{{ route('barang.index') }}" class="btn-reset">↻ Reset</a>
    </form>

    {{-- Form tersembunyi untuk hapus massal (checkbox memakai atribut form="bulkForm") --}}
    <form id="bulkForm" action="{{ route('barang.bulk-destroy') }}" method="POST">
        @csrf @method('DELETE')
    </form>

    <div class="bulk-bar" id="bulkBar">
        <span><strong id="bulkCount">0</strong> barang dipilih</span>
        <button type="submit" form="bulkForm" class="btn-bulk"
                onclick="return confirm('Hapus semua barang yang dipilih?')">Hapus Terpilih</button>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" id="checkAll"></th>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $p)
                <tr>
                    <td class="col-check">
                        <input type="checkbox" class="row-check" name="ids[]" value="{{ $p->id }}" form="bulkForm">
                    </td>
                    <td>{{ $products->firstItem() + $loop->index }}</td>
                    <td>
                        <span class="p-thumb">
                            @if ($p->gambar && file_exists(public_path('images/produk/' . $p->gambar)))
                                <img src="{{ asset('images/produk/' . $p->gambar) }}" alt="{{ $p->nama }}">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>
                                </svg>
                            @endif
                        </span>
                    </td>
                    <td>
                        <div class="p-name">{{ $p->nama }}</div>
                        <small class="p-sku">SKU : {{ $p->kode }}</small>
                    </td>
                    <td><span class="cat cat-{{ \Illuminate\Support\Str::slug($p->kategori) }}">{{ $p->kategori }}</span></td>
                    <td>Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                    <td><strong>{{ $p->stok }}</strong></td>
                    <td><span class="st st-{{ $p->status }}">{{ $p->status_label }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="act act-edit btn-edit" title="Edit"
                                    data-url="{{ route('barang.update', $p) }}"
                                    data-product="{{ json_encode($p->only(['id','kode','nama','kategori','harga_beli','harga_jual','stok','stok_minimum'])) }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                            </button>
                            <form action="{{ route('barang.destroy', $p) }}" method="POST"
                                  onsubmit="return confirm('Hapus barang {{ addslashes($p->nama) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="act act-del" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="empty">Belum ada barang yang sesuai.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-foot">
        <span class="foot-info">
            Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} data
        </span>
        {{ $products->links('partials.pagination') }}
    </div>
</section>

{{-- Modal tambah / edit --}}
<div class="modal" id="productModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalTitle">Tambah Barang</h3>
            <button type="button" class="modal-close" id="modalClose" aria-label="Tutup">&times;</button>
        </div>

        <form id="productForm" action="{{ route('barang.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="PUT" id="methodField" disabled>
            <input type="hidden" name="form_mode" id="formMode" value="{{ old('form_mode', 'tambah') }}">
            <input type="hidden" name="edit_id" id="editId" value="{{ old('edit_id') }}">

            @if ($errors->any() && old('form_mode'))
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-grid">
                <div class="fg full">
                    <label for="f_nama">Nama Produk</label>
                    <input type="text" id="f_nama" name="nama" value="{{ old('nama') }}" placeholder="Contoh: Wardah Lightening Moisturizer">
                </div>

                <div class="fg">
                    <label for="f_kode">Kode Barang (SKU)</label>
                    <input type="text" id="f_kode" name="kode" value="{{ old('kode') }}" placeholder="Kosongkan = otomatis">
                </div>

                <div class="fg">
                    <label for="f_kategori">Kategori</label>
                    <select id="f_kategori" name="kategori">
                        @foreach ($kategoriList as $k)
                            <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="fg">
                    <label for="f_harga_beli">Harga Beli (Rp)</label>
                    <input type="number" min="0" id="f_harga_beli" name="harga_beli" value="{{ old('harga_beli') }}">
                </div>

                <div class="fg">
                    <label for="f_harga_jual">Harga Jual (Rp)</label>
                    <input type="number" min="0" id="f_harga_jual" name="harga_jual" value="{{ old('harga_jual') }}">
                </div>

                <div class="fg">
                    <label for="f_stok">Stok</label>
                    <input type="number" min="0" id="f_stok" name="stok" value="{{ old('stok') }}">
                </div>

                <div class="fg">
                    <label for="f_stok_minimum">Batas Stok Menipis</label>
                    <input type="number" min="0" id="f_stok_minimum" name="stok_minimum" value="{{ old('stok_minimum', 10) }}">
                </div>

                <div class="fg full">
                    <label for="f_gambar">Gambar Produk <small>(jpg/png/webp, maks 2 MB. Kosongkan jika tidak diganti)</small></label>
                    <input type="file" id="f_gambar" name="gambar" accept="image/*">
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn-cancel" id="modalCancel">Batal</button>
                <button type="submit" class="btn-add">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ===== Modal tambah / edit =====
    const modal     = document.getElementById('productModal');
    const form      = document.getElementById('productForm');
    const methodFld = document.getElementById('methodField');
    const modeFld   = document.getElementById('formMode');
    const editIdFld = document.getElementById('editId');
    const titleEl   = document.getElementById('modalTitle');
    const storeUrl  = @json(route('barang.store'));

    const fields   = ['nama', 'kode', 'kategori', 'harga_beli', 'harga_jual', 'stok', 'stok_minimum'];
    const defaults = { kategori: 'Skincare', stok_minimum: 10 };

    function openModal(mode, data = null, url = null, restore = false) {
        const isEdit = mode === 'edit';

        titleEl.textContent = isEdit ? 'Edit Barang' : 'Tambah Barang';
        form.action         = isEdit ? url : storeUrl;
        methodFld.disabled  = !isEdit;
        modeFld.value       = isEdit ? 'edit' : 'tambah';
        editIdFld.value     = isEdit && data ? data.id : (restore ? editIdFld.value : '');

        // restore = true: buka kembali setelah error validasi, isi form dipertahankan dari old()
        if (!restore) {
            fields.forEach(f => {
                form.elements[f].value = data ? (data[f] ?? '') : (defaults[f] ?? '');
            });
            form.elements['gambar'].value = '';
        }

        modal.classList.add('show');
    }

    function closeModal() { modal.classList.remove('show'); }

    document.getElementById('btnAdd').addEventListener('click', () => openModal('tambah'));
    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('modalCancel').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal('edit', JSON.parse(btn.dataset.product), btn.dataset.url);
        });
    });

    // Buka lagi modal jika ada error validasi
    @if ($errors->any() && old('form_mode'))
        openModal(
            @json(old('form_mode')),
            null,
            @json(old('edit_id') ? route('barang.update', old('edit_id')) : null),
            true
        );
    @endif

    // ===== Pilih banyak + hapus massal =====
    const checkAll  = document.getElementById('checkAll');
    const bulkBar   = document.getElementById('bulkBar');
    const bulkCount = document.getElementById('bulkCount');
    const rowChecks = () => [...document.querySelectorAll('.row-check')];

    function updateBulk() {
        const n = rowChecks().filter(c => c.checked).length;
        bulkCount.textContent = n;
        bulkBar.classList.toggle('show', n > 0);
        checkAll.checked = n > 0 && n === rowChecks().length;
    }

    checkAll.addEventListener('change', () => {
        rowChecks().forEach(c => c.checked = checkAll.checked);
        updateBulk();
    });
    rowChecks().forEach(c => c.addEventListener('change', updateBulk));
</script>
@endpush