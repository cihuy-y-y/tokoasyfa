<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Dashboard Admin</title></head>
<body>
    <h1>Selamat datang, {{ auth()->user()->name }}!</h1>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Logout</button>
        <button type="button" onclick="window.location.href='{{ route('owner.users') }}'">Kelola Pengguna</button>
    </form>
</body>
</html>