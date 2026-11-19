<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PengaturanController extends Controller
{
    private const PREF_KEYS = [
        'tema_warna',
        'notif_transaksi',
        'notif_stok_menipis',
        'notif_stok_habis',
        'notif_laporan',
    ];

    public function preferensi(Request $request)
{
    $validated = $request->validate([
        'key' => ['required', 'string', Rule::in(self::PREF_KEYS)],
        'value' => ['required', 'string'],
    ]);

    $key = $validated['key'];
    $value = $validated['value'];

    // Validasi tema warna
    if ($key === 'tema_warna' && !array_key_exists($value, Setting::TEMA)) {
        return response()->json([
            'success' => false,
            'message' => 'Tema warna tidak valid.'
        ], 422);
    }

    // Validasi notifikasi
    $notifKeys = [
        'notif_transaksi',
        'notif_stok_menipis',
        'notif_stok_habis',
        'notif_laporan',
    ];

    if (in_array($key, $notifKeys, true) && !in_array($value, ['0', '1'], true)) {
        return response()->json([
            'success' => false,
            'message' => 'Nilai notifikasi tidak valid.'
        ], 422);
    }

    Setting::simpan($key, $value);

    return response()->json([
        'success' => true,
        'message' => 'Preferensi berhasil disimpan.'
    ]);
}

    public function index(Request $request)
    {
        $tabs = ['toko' => 'Informasi Toko', 'profil' => 'Profil', 'password' => 'Kata Sandi'];

        if ($request->user()->isOwner()) {
            $tabs['pembayaran'] = 'Payment Gateway';
            $tabs['backup']     = 'Backup Data';
        }

        $tab = $request->query('tab', 'toko');
        if (! array_key_exists($tab, $tabs)) {
            $tab = 'toko';
        }

        return view('pengaturan.index', [
            'tabs' => $tabs,
            'tab'  => $tab,
            's'    => Setting::semua(),
        ]);
    }

    // ===== Informasi Toko =====

    public function updateToko(Request $request)
    {
        $data = $request->validate([
            'nama_toko'       => ['required', 'string', 'max:100'],
            'alamat'          => ['required', 'string', 'max:255'],
            'telepon'         => ['required', 'string', 'max:30'],
            'email'           => ['required', 'email', 'max:255'],
            'jam_operasional' => ['required', 'string', 'max:50'],
            'tentang'         => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_toko.required'       => 'Nama toko wajib diisi.',
            'alamat.required'          => 'Alamat wajib diisi.',
            'telepon.required'         => 'No. telepon wajib diisi.',
            'email.required'           => 'Email wajib diisi.',
            'email.email'              => 'Format email tidak valid.',
            'jam_operasional.required' => 'Jam operasional wajib diisi.',
            'max'                      => ':attribute terlalu panjang (maks :max karakter).',
        ]);

        Setting::simpanBanyak($data);

        return redirect()->route('pengaturan.index')->with('success', 'Informasi toko berhasil diperbarui.');
    }

    // ===== Profil & Kata Sandi =====

    public function updateProfil(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:4', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required'       => 'Nama lengkap wajib diisi.',
            'username.required'   => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, strip, dan underscore.',
            'username.min'        => 'Username minimal 4 karakter.',
            'username.unique'     => 'Username sudah dipakai.',
            'email.required'      => 'Email wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'email.unique'        => 'Email sudah terdaftar.',
        ]);

        $user->update($data);

        return redirect()->route('pengaturan.index', ['tab' => 'profil'])->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required'         => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'password.required'                 => 'Password baru wajib diisi.',
            'password.min'                      => 'Password baru minimal 8 karakter.',
            'password.confirmed'                => 'Konfirmasi password tidak cocok.',
            'password.different'                => 'Password baru harus berbeda dari password saat ini.',
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('pengaturan.index', ['tab' => 'password'])->with('success', 'Password berhasil diganti.');
    }

    // ===== Payment Gateway (owner) =====

    public function updatePembayaran(Request $request)
    {
        $data = $request->validate([
            'bank_nama'      => ['nullable', 'string', 'max:50'],
            'bank_rekening'  => ['nullable', 'string', 'max:50'],
            'bank_atas_nama' => ['nullable', 'string', 'max:100'],
            'ewallet_nomor'  => ['nullable', 'string', 'max:30'],
        ]);

        foreach (['metode_qris', 'metode_transfer', 'metode_ewallet', 'metode_tunai'] as $k) {
            $data[$k] = $request->boolean($k) ? '1' : '0';
        }

        Setting::simpanBanyak($data);

        return redirect()->route('pengaturan.index', ['tab' => 'pembayaran'])->with('success', 'Pengaturan pembayaran disimpan.');
    }

    // ===== Backup Data (owner) =====

    // File .sql berisi data (INSERT) semua tabel. Struktur tabel dibuat oleh migration.
    public function backup()
    {
        $lewati = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'];
        $nama   = 'backup-asya-' . now()->format('Ymd-His') . '.sql';

        $path = $folder . DIRECTORY_SEPARATOR . $namaFile;

        $handle = fopen($path, 'wb');

        if ($handle === false) {
            abort(500, 'Gagal membuat file backup.');
        }

        try {
            $this->tulisDump(function ($isi) use ($handle) {
                if (fwrite($handle, $isi) === false) {
                    throw new \RuntimeException(
                        'Gagal menulis file backup.'
                    );
                }
            });

            fflush($handle);

        } catch (\Throwable $e) {

            fclose($handle);

            File::delete($path);

            report($e);

            throw $e;
        }

        fclose($handle);

        Setting::simpan(
            'last_backup_at',
            now()->toDateTimeString()
        );

        return response()->streamDownload(function () use ($lewati) {
            $pdo   = DB::getPdo();
            $mysql = DB::getDriverName() === 'mysql';
            $q     = $mysql ? '`' : '"';

            echo "-- Backup Toko Kosmetik Asya\n-- Dibuat: " . now()->format('d/m/Y H:i:s') . "\n\n";
            if ($mysql) {
                echo "SET FOREIGN_KEY_CHECKS=0;\n\n";
            }

            foreach (Schema::getTableListing() as $tabel) {
                if (in_array($tabel, $lewati, true)) {
                    continue;
                }

                echo "-- Tabel: {$tabel}\n";

                foreach (DB::table($tabel)->cursor() as $baris) {
                    $baris  = (array) $baris;
                    $kolom  = implode(', ', array_map(fn ($c) => $q . $c . $q, array_keys($baris)));
                    $nilai  = implode(', ', array_map(
                        fn ($v) => $v === null ? 'NULL' : (is_int($v) || is_float($v) ? $v : $pdo->quote((string) $v)),
                        array_values($baris)
                    ));

                    echo "INSERT INTO {$q}{$tabel}{$q} ({$kolom}) VALUES ({$nilai});\n";
                }

                echo "\n";
            }

            if ($mysql) {
                echo "SET FOREIGN_KEY_CHECKS=1;\n";
            }
        }, $nama, ['Content-Type' => 'application/sql; charset=UTF-8']);
    }
}
