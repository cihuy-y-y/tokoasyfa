<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - Toko Kosmetik Asya</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/owner.css') }}">
</head>
<body>
<div class="container">

    <header class="topbar">
        <div>
            <h1>Kelola Akun</h1>
            <p>Login sebagai <strong>{{ auth()->user()->name }}</strong> (Owner)</p>
        </div>
        <div class="topbar-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Dashboard</a>
            <a href="{{ route('pos.index') }}" class="btn btn-ghost">POS</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline">Logout</button>
            </form>
        </div>
    </header>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="alert alert-error">{{ session('error') }}</div>   @endif
    @error('role')           <div class="alert alert-error">{{ $message }}</div>            @enderror

    {{-- Menunggu persetujuan --}}
    <section class="card">
        <h2>Menunggu Persetujuan <span class="badge badge-warn">{{ $pending->count() }}</span></h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Nama</th><th>Username</th><th>Email</th><th>Daftar</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($pending as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->username }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <div class="actions">
                                <form action="{{ route('owner.users.approve', $u) }}" method="POST" class="inline-form">
                                    @csrf @method('PATCH')
                                    <select name="role" required>
                                        <option value="" disabled selected>Pilih role</option>
                                        <option value="admin">Admin (Dashboard)</option>
                                        <option value="kasir">Kasir (POS)</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Setujui</button>
                                </form>
                                <form action="{{ route('owner.users.reject', $u) }}" method="POST"
                                      onsubmit="return confirm('Tolak pendaftaran {{ $u->username }}?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-danger">Tolak</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Tidak ada pendaftaran yang menunggu.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Akun aktif --}}
    <section class="card">
        <h2>Akun Aktif <span class="badge badge-ok">{{ $approved->count() }}</span></h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Nama</th><th>Username</th><th>Role</th><th>Ubah Role</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($approved as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->username }}</td>
                        <td><span class="badge badge-role">{{ ucfirst($u->role) }}</span></td>
                        <td>
                            <form action="{{ route('owner.users.approve', $u) }}" method="POST" class="inline-form">
                                @csrf @method('PATCH')
                                <select name="role">
                                    <option value="admin" @selected($u->role === 'admin')>Admin</option>
                                    <option value="kasir" @selected($u->role === 'kasir')>Kasir</option>
                                </select>
                                <button type="submit" class="btn btn-outline">Simpan</button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('owner.users.deactivate', $u) }}" method="POST"
                                  onsubmit="return confirm('Nonaktifkan akun {{ $u->username }}?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-danger">Nonaktifkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada akun aktif.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Ditolak --}}
    @if ($rejected->count())
    <section class="card">
        <h2>Ditolak <span class="badge badge-danger">{{ $rejected->count() }}</span></h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Aksi</th></tr></thead>
                <tbody>
                @foreach ($rejected as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->username }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <form action="{{ route('owner.users.approve', $u) }}" method="POST" class="inline-form">
                                @csrf @method('PATCH')
                                <select name="role" required>
                                    <option value="" disabled selected>Pilih role</option>
                                    <option value="admin">Admin</option>
                                    <option value="kasir">Kasir</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Setujui</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif

</div>
</body>
</html>