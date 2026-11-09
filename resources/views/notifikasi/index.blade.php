@extends('layouts.app')

@section('title', 'Notifikasi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barang.css') }}">
    <link rel="stylesheet" href="{{ asset('css/notifikasi.css') }}">
@endpush

@section('content')
@php
    $ikon = [
        'stok'      => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/>',
        'habis'     => '<circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/>',
        'transaksi' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/>',
        'laporan'   => '<path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
    ];

    $tabs = ['semua' => 'Semua', 'baru' => 'Belum dibaca', 'stok' => 'Stok', 'transaksi' => 'Transaksi', 'laporan' => 'Laporan'];
    if (auth()->user()->isOwner()) $tabs['user'] = 'Pendaftar';
@endphp

<div class="page-bar">
    <div class="page-title">
        <span class="page-ico round">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.9 1.9 0 0 0 3.4 0"/>
            </svg>
        </span>
        <div>
            <h1>Notifikasi</h1>
            <p>Semua pemberitahuan penting toko Anda.</p>
        </div>
    </div>

    @if ($hitung['baru'] > 0)
        <form action="{{ route('notifikasi.baca') }}" method="POST">
            @csrf
            <button type="submit" class="btn-add">✓ Tandai semua dibaca</button>
        </form>
    @endif
</div>

<section class="panel">
    <div class="chips">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('notifikasi.index', ['jenis' => $key]) }}" class="chip {{ $jenis === $key ? 'active' : '' }}">
                {{ $label }} <span class="chip-n">{{ $hitung[$key] }}</span>
            </a>
        @endforeach
    </div>

    <ul class="nf-list">
        @forelse ($items as $n)
            <li>
                <a href="{{ $n['url'] }}" class="nf-item {{ $n['baru'] ? 'unread' : '' }}">
                    <span class="notif-ico ni-{{ $n['ikon'] }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $ikon[$n['ikon']] !!}</svg>
                    </span>
                    <span class="nf-text">
                        <strong>{{ $n['judul'] }}</strong>
                        <small>{{ $n['pesan'] }}</small>
                    </span>
                    <span class="nf-time">
                        {{ $n['waktu'] }}
                        <em>{{ $n['waktu_penuh'] }}</em>
                        @if ($n['baru']) <b class="nf-dot"></b> @endif
                    </span>
                </a>
            </li>
        @empty
            <li class="nf-empty">Tidak ada notifikasi.</li>
        @endforelse
    </ul>

    @if ($items->total() > 0)
        <div class="table-foot">
            <span class="foot-info">
                Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} notifikasi
            </span>
            {{ $items->links('partials.pagination') }}
        </div>
    @endif
</section>
@endsection