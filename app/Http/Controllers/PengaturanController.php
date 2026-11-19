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
use Illuminate\Support\Facades\Auth;

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

        foreach (['metode_qris', 'metode_ewallet', 'metode_tunai'] as $k) {
            $data[$k] = $request->boolean($k) ? '1' : '0';
        }

        Setting::simpanBanyak($data);

        return redirect()->route('pengaturan.index', ['tab' => 'pembayaran'])->with('success', 'Pengaturan pembayaran disimpan.');
    }

    // ===== Backup Data (owner) =====

    // File .sql berisi data (INSERT) semua tabel. Struktur tabel dibuat oleh migration.
    private const LEWATI = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'];
         // Nama tabel data (tanpa awalan schema)
    private function tabelData(): array
    {
        $db = DB::connection('mysql');

        return collect($db->getSchemaBuilder()->getTableListing())
            ->map(fn ($t) => Str::afterLast($t, '.'))
            ->reject(fn ($t) => in_array($t, self::LEWATI, true))
            ->unique()
            ->filter(fn ($t) => $db->getSchemaBuilder()->hasTable($t))
            ->values()
            ->all();
    }

    // Menulis isi backup lewat callback (dipakai untuk unduhan dan cadangan otomatis)
    private function tulisDump(callable $tulis): void
    {
        $db = DB::connection('mysql');
        $pdo = $db->getPdo();

        $tulis("-- Backup Toko Kosmetik Asya\n-- Dibuat: " . now()->format('d/m/Y H:i:s') . "\n\n");
        $tulis("SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($this->tabelData() as $tabel) {
            $tulis("-- Tabel: {$tabel}\n"); 

            foreach ($db->table($tabel)->cursor() as $baris) {
                $baris = (array) $baris;
                $kolom = implode(', ', array_map(fn ($c) => '`' . $c . '`', array_keys($baris)));
                $nilai = implode(', ', array_map(
                    fn ($v) => $v === null ? 'NULL' : (is_int($v) || is_float($v) ? $v : $pdo->quote((string) $v)),
                    array_values($baris)
                ));

                $tulis("INSERT INTO `{$tabel}` ({$kolom}) VALUES ({$nilai});\n");
            }

            $tulis("\n");
        }

        $tulis("SET FOREIGN_KEY_CHECKS=1;\n");
    }

    public function backup()
    {
        abort_unless(
            DB::getDriverName() === 'mysql',
            400,
            'Backup hanya mendukung MySQL/MariaDB.'
        );

        set_time_limit(300);

        $folder = storage_path('app/backups');

        File::ensureDirectoryExists($folder);

        $namaFile = 'backup-asya-' . now()->format('Ymd-His') . '.sql';

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

        return response()->download(
            $path,
            $namaFile,
            ['Content-Type' => 'application/sql; charset=UTF-8']
        )->deleteFileAfterSend(true);
    }
    
    // ===== Impor / Restore (owner) =====

    public function impor(Request $request)
    {
        $request->validate([
            'file'       => ['required', 'file', 'extensions:sql,txt', 'max:20480'],   // 20 MB
            'konfirmasi' => ['required', 'in:IMPOR'],
        ], [
            'file.required'      => 'Pilih file backup (.sql) terlebih dahulu.',
            'file.extensions'    => 'File harus berformat .sql.',
            'file.max'           => 'Ukuran file maksimal 20 MB.',
            'file.uploaded'      => 'Gagal mengunggah. Naikkan upload_max_filesize dan post_max_size di php.ini.',
            'konfirmasi.required'=> 'Ketik IMPOR untuk konfirmasi.',
            'konfirmasi.in'      => 'Konfirmasi salah. Ketik persis: IMPOR',
        ]);

        if (DB::getDriverName() !== 'mysql') {
            return back()->withErrors(['file' => 'Impor hanya mendukung MySQL/MariaDB.']);
        }

        $isi = file_get_contents($request->file('file')->getRealPath());

        if (! str_contains(substr($isi, 0, 500), 'Backup Toko Kosmetik Asya')) {
            return back()->withErrors(['file' => 'File ini bukan hasil backup dari aplikasi ini.']);
        }

        // ---- Periksa semua perintah sebelum menyentuh database ----
        $izin   = $this->tabelData();
        $daftar = [];
        $tabel  = [];

        foreach ($this->pecahSql($isi) as $stmt) {
            if (preg_match('/^SET\s+FOREIGN_KEY_CHECKS\s*=\s*[01]$/i', $stmt)) {
                continue;   // diatur sendiri oleh proses impor
            }

            if (! preg_match('/^INSERT\s+INTO\s+[`"]?([A-Za-z0-9_]+)[`"]?\s*\(/i', $stmt, $m) || ! in_array($m[1], $izin, true)) {
                return back()->withErrors(['file' => 'File ditolak: berisi perintah yang tidak diizinkan.']);
            }

            $tabel[$m[1]] = true;
            $daftar[]     = $stmt;
        }

        if (! $daftar) {
            return back()->withErrors(['file' => 'File tidak berisi data yang bisa diimpor.']);
        }

        set_time_limit(300);

        // ---- Cadangan otomatis kondisi saat ini ----
        $cadangan = storage_path('app/backups/sebelum-impor-' . now()->format('Ymd-His') . '.sql');
        File::ensureDirectoryExists(dirname($cadangan));
        $h = fopen($cadangan, 'w');
        $this->tulisDump(function ($s) use ($h) { fwrite($h, $s); });
        fclose($h);

        // ---- Pulihkan (semua-atau-tidak-sama-sekali) ----
        try {
            $db = DB::connection();   // satu koneksi yang sama untuk semua perintah
            $db->statement('SET FOREIGN_KEY_CHECKS=0');
        
            foreach (array_keys($tabel) as $t) {
                $db->statement('TRUNCATE TABLE `' . $t . '`');
            }
        
            foreach ($daftar as $stmt) {
                $db->unprepared($stmt);
            }
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'file' => 'Impor gagal. Data lama bisa dipulihkan dari storage/app/backups. ' . Str::limit($e->getMessage(), 200),
            ]);
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Akun bisa berubah mengikuti backup, jadi minta login ulang
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Impor berhasil. Silakan login dengan akun dari data backup.');
    }

    // Memecah file SQL menjadi perintah; titik koma dan "--" di dalam teks bertanda kutip tidak ikut terpecah
    private function pecahSql(string $sql): array
    {
        $hasil = [];
        $buf   = '';
        $kutip = false;
        $n     = strlen($sql);

        for ($i = 0; $i < $n; $i++) {
            $c = $sql[$i];

            if ($kutip) {
                $buf .= $c;
                if ($c === '\\' && $i + 1 < $n) {
                    $buf .= $sql[++$i];          // karakter yang di-escape
                } elseif ($c === "'") {
                    $kutip = false;
                }
                continue;
            }

            if ($c === "'") {
                $kutip = true;
                $buf  .= $c;
                continue;
            }

            if ($c === '-' && ($sql[$i + 1] ?? '') === '-') {   // komentar sampai akhir baris
                $akhir = strpos($sql, "\n", $i);
                $i     = $akhir === false ? $n : $akhir;
                continue;
            }

            if ($c === ';') {
                $s = trim($buf);
                if ($s !== '') $hasil[] = $s;
                $buf = '';
                continue;
            }

            $buf .= $c;
        }

        $s = trim($buf);
        if ($s !== '') $hasil[] = $s;

        return $hasil;
    }
}