<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Toko Kosmetik Asya</title></head>
<body>
    <h1>Toko Kosmetik Asya - Cantik Setiap Hari</h1>

    @auth
        <p>Anda login sebagai {{ auth()->user()->name }}.</p>
        <a href="{{ route(auth()->user()->homeRoute()) }}">Masuk ke halaman saya</a>

        <form action="{{ route('logout') }}" method="POST" style="display:inline">
            @csrf
            <button type="submit">Logout</button>
        </form>
    @else
        <a href="{{ route('login') }}">Login Admin</a>
    @endauth
</body>
</html>