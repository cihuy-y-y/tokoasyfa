@extends('layouts.app')

@section('title', 'Pengguna')

@push('styles')
    {{-- dashboard.css = kartu ringkasan, barang.css = tabel/filter/modal --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barang.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner.css') }}">
@endpush

@section('content')

{{-- Judul halaman --}}
<div class="page-bar">
    <div class="page-title">
        <span class="page-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
            </svg>
        </span>
        <div>
            <h1>Pengguna</h1>
            <p>Kelola akun admin dan kasir toko kosmetik Asyfa</p>
        </div>
    </div>
    <button type="button" class="btn-add" id="btnAdd">+ Tambah Pengguna</button>
</div>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if (session('error'))   <div class="alert alert-error">{{ session('error') }}</div>   @endif
@if ($errors->any() && ! old('form_mode'))
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

@if ($summary['pending'] > 0)
    <div class="alert alert-warn">
        Ada <strong>{{ $summary['pending'] }}</strong> pendaftar baru yang menunggu persetujuan.
        <a href="{{ route('owner.users', ['status' => 'pending']) }}">Lihat sekarang</a>
    </div>
@endif

{{-- Ringkasan --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon ic-pink">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Total Pengguna</span>
            <strong class="stat-value">{{ $summary['total'] }}</strong>
            <span class="stat-note">Akun terdaftar di sistem</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="7" r="4"/><path d="M3 21c0-4 3-6 7-6M16 17v-1.5a2 2 0 0 1 4 0V17M15 17h6v4h-6z"/></svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Admin</span>
            <strong class="stat-value">{{ $summary['admin'] }}</strong>
            <span class="stat-note">Akun dengan akses penuh</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="7" r="4"/><path d="M3 21c0-4 3-6 7-6M18 15v6M15 18h6"/></svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Kasir</span>
            <strong class="stat-value">{{ $summary['kasir'] }}</strong>
            <span class="stat-note">Akun dengan akses transaksi</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
        </div>
        <div class="stat-body">
            <span class="stat-label">Akun Aktif</span>
            <strong class="stat-value">{{ $summary['aktif'] }}</strong>
            <span class="stat-note">Akun yang bisa login</span>
        </div>
    </div>
</div>

{{-- Tabel --}}
<section class="panel">

    <form method="GET" action="{{ route('owner.users') }}" class="filter-bar">
        <div class="f-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, username, atau role...">
        </div>

        <select name="role" onchange="this.form.submit()">
            <option value="">Semua Role</option>
            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
            <option value="kasir" @selected(request('role') === 'kasir')>Kasir</option>
        </select>

        <a href="{{ route('owner.users') }}" class="btn-reset">↻ Reset</a>
    </form>

    {{-- Form tersembunyi hapus massal --}}
    <form id="bulkForm" action="{{ route('owner.users.bulk-destroy') }}" method="POST">
        @csrf @method('DELETE')
    </form>

    <div class="bulk-bar" id="bulkBar">
        <span><strong id="bulkCount">0</strong> pengguna dipilih</span>
        <button type="submit" form="bulkForm" class="btn-bulk"
                onclick="return confirm('Hapus semua pengguna yang dipilih?')">Hapus Terpilih</button>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" id="checkAll"></th>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($users as $u)
                @php
                    $warna   = ['#e98aa9', '#a78bfa', '#f59e0b', '#34d399', '#60a5fa'][$u->id % 5];
                    $inisial = strtoupper(mb_substr($u->name, 0, 1));
                    $statusLabel = ['approved' => 'Aktif', 'pending' => 'Menunggu', 'rejected' => 'Ditolak'][$u->status] ?? $u->status;
                @endphp
                <tr>
                    <td class="col-check">
                        <input type="checkbox" class="row-check" name="ids[]" value="{{ $u->id }}" form="bulkForm">
                    </td>
                    <td>{{ $users->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="u-cell">
                            <span class="u-avatar" style="background: {{ $warna }}">{{ $inisial }}</span>
                            <div>
                                <div class="p-name">{{ $u->name }}</div>
                                <small class="p-sku">{{ $u->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $u->username }}</td>
                    <td>
                        @if ($u->role)
                            <span class="role role-{{ $u->role }}">{{ ucfirst($u->role) }}</span>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td><span class="st st-{{ $u->status }}">{{ $statusLabel }}</span></td>
                    <td>
                        <div class="row-actions">

                            @if ($u->status === 'approved')
                                {{-- Aktif: edit --}}
                                <button type="button" class="act btn-edit" title="Edit"
                                        data-url="{{ route('owner.users.update', $u) }}"
                                        data-user="{{ json_encode($u->only(['id', 'name', 'username', 'email', 'role', 'status'])) }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                </button>
                            @else
                                {{-- Menunggu / ditolak: setujui dengan role --}}
                                <form action="{{ route('owner.users.approve', $u) }}" method="POST" class="approve-form">
                                    @csrf @method('PATCH')
                                    <select name="role" required>
                                        <option value="" disabled selected>Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="kasir">Kasir</option>
                                    </select>
                                    <button type="submit" class="btn-ok">Setujui</button>
                                </form>

                                @if ($u->status === 'pending')
                                    <form action="{{ route('owner.users.reject', $u) }}" method="POST"
                                          onsubmit="return confirm('Tolak pendaftaran {{ addslashes($u->username) }}?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-no">Tolak</button>
                                    </form>
                                @endif
                            @endif

                            <form action="{{ route('owner.users.destroy', $u) }}" method="POST"
                                  onsubmit="return confirm('Hapus pengguna {{ addslashes($u->username) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="act act-del" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty">Belum ada pengguna yang sesuai.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-foot">
        <span class="foot-info">
            Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data
        </span>
        {{ $users->links('partials.pagination') }}
    </div>
</section>

{{-- Modal tambah / edit --}}
<div class="modal" id="userModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalTitle">Tambah Pengguna</h3>
            <button type="button" class="modal-close" id="modalClose" aria-label="Tutup">&times;</button>
        </div>

        <form id="userForm" action="{{ route('owner.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" value="PUT" id="methodField" disabled>
            <input type="hidden" name="form_mode" id="formMode" value="{{ old('form_mode', 'tambah') }}">
            <input type="hidden" name="edit_id" id="editId" value="{{ old('edit_id') }}">

            @if ($errors->any() && old('form_mode'))
                <div class="alert alert-error">
                    <ul>@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                </div>
            @endif

            <div class="form-grid">
                <div class="fg full">
                    <label for="f_name">Nama Lengkap</label>
                    <input type="text" id="f_name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap">
                </div>

                <div class="fg">
                    <label for="f_username">Username</label>
                    <input type="text" id="f_username" name="username" value="{{ old('username') }}" placeholder="Masukkan username">
                </div>

                <div class="fg">
                    <label for="f_email">Email</label>
                    <input type="email" id="f_email" name="email" value="{{ old('email') }}" placeholder="Masukkan email">
                </div>

                <div class="fg full">
                    <label for="f_role">Role</label>
                    <select id="f_role" name="role">
                        <option value="admin" @selected(old('role') === 'admin')>Admin (Dashboard)</option>
                        <option value="kasir" @selected(old('role') === 'kasir')>Kasir (POS)</option>
                    </select>
                </div>

                <div class="fg full">
                    <label for="f_password">Password <small id="pwHint"></small></label>
                    <div class="pw-wrap">
                        <input type="password" id="f_password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password">
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Tampilkan password">
                            <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                <path d="M17.9 17.9A10.9 10.9 0 0 1 12 19C5 19 1 12 1 12a18.7 18.7 0 0 1 5.1-5.9M9.9 5.1A10.4 10.4 0 0 1 12 5c7 0 11 7 11 7a18.5 18.5 0 0 1-2.2 3.2M14.1 14.1a3 3 0 1 1-4.2-4.2M1 1l22 22"/>
                            </svg>
                        </button>
                    </div>
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
    const modal       = document.getElementById('userModal');
    const form        = document.getElementById('userForm');
    const methodFld   = document.getElementById('methodField');
    const modeFld     = document.getElementById('formMode');
    const editIdFld   = document.getElementById('editId');
    const titleEl     = document.getElementById('modalTitle');
    const pwHint      = document.getElementById('pwHint');
    const storeUrl    = @json(route('owner.users.store'));

    function openModal(mode, data = null, url = null, restore = false) {
        const isEdit = mode === 'edit';

        titleEl.textContent = isEdit ? 'Edit Pengguna' : 'Tambah Pengguna';
        form.action         = isEdit ? url : storeUrl;
        methodFld.disabled  = !isEdit;
        modeFld.value       = isEdit ? 'edit' : 'tambah';
        editIdFld.value     = isEdit && data ? data.id : (restore ? editIdFld.value : '');

        pwHint.textContent = isEdit ? '(kosongkan jika tidak diganti)' : '';

        if (!restore) {
            form.elements['name'].value     = data ? data.name : '';
            form.elements['username'].value = data ? data.username : '';
            form.elements['email'].value    = data ? data.email : '';
            form.elements['role'].value     = data && data.role ? data.role : 'admin';
            form.elements['password'].value = '';
        }

        modal.classList.add('show');
    }

    function closeModal() { modal.classList.remove('show'); }

     // ===== Tampilkan / sembunyikan password =====
    const pwInput  = document.getElementById('f_password');
    const pwToggle = document.getElementById('pwToggle');

    function resetPwIcon() {
        pwInput.type = 'password';
        pwToggle.querySelector('.eye-on').style.display  = '';
        pwToggle.querySelector('.eye-off').style.display = 'none';
    }

    pwToggle.addEventListener('click', () => {
        const tampil = pwInput.type === 'password';
        pwInput.type = tampil ? 'text' : 'password';
        pwToggle.querySelector('.eye-on').style.display  = tampil ? 'none' : '';
        pwToggle.querySelector('.eye-off').style.display = tampil ? '' : 'none';
    });

    document.getElementById('btnAdd').addEventListener('click', () => openModal('tambah'));
    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('modalCancel').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal('edit', JSON.parse(btn.dataset.user), btn.dataset.url);
        });
    });

    // Buka lagi modal jika ada error validasi
    @if ($errors->any() && old('form_mode'))
        openModal(
            @json(old('form_mode')),
            null,
            @json(old('edit_id') ? route('owner.users.update', old('edit_id')) : null),
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