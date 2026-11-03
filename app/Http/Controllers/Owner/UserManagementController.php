<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::bukanOwner();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('username', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('role', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role'))   $query->where('role', $request->role);
        if ($request->filled('status')) $query->where('status', $request->status);

        // Pendaftar baru (pending) tampil paling atas
        $users = $query->orderByRaw("status = 'pending' desc")
                       ->latest()
                       ->paginate(10)
                       ->withQueryString();

        $summary = [
            'total'   => User::bukanOwner()->count(),
            'admin'   => User::where('role', 'admin')->where('status', 'approved')->count(),
            'kasir'   => User::where('role', 'kasir')->where('status', 'approved')->count(),
            'aktif'   => User::bukanOwner()->where('status', 'approved')->count(),
            'pending' => User::where('status', 'pending')->count(),
        ];

        return view('owner.users', compact('users', 'summary'));
    }

    // Tambah pengguna langsung aktif
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:4', 'max:50', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', Rule::in(['admin', 'kasir'])],
            'password' => ['required', 'string', 'min:8'],
        ], $this->messages());

        User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'status'   => 'approved',
        ]);

        return redirect()->route('owner.users')->with('success', "Pengguna {$data['username']} berhasil ditambahkan.");
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->isOwner(), 403);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:4', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'status'   => ['required', Rule::in(['approved', 'pending', 'rejected'])],
            'role'     => ['required_if:status,approved', 'nullable', Rule::in(['admin', 'kasir'])],
            'password' => ['nullable', 'string', 'min:8'],
        ], $this->messages());

        $payload = [
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'status'   => $data['status'],
            // Role hanya berlaku untuk akun aktif
            'role'     => $data['status'] === 'approved' ? $data['role'] : null,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return back()->with('success', "Data {$user->username} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        abort_if($user->isOwner(), 403);

        $user->delete();

        return back()->with('success', "Pengguna {$user->username} dihapus.");
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ], ['ids.required' => 'Pilih pengguna yang ingin dihapus.']);

        $jumlah = User::bukanOwner()->whereIn('id', $request->ids)->delete();

        return back()->with('success', "{$jumlah} pengguna berhasil dihapus.");
    }

    // Setujui + tentukan role (dipakai untuk pending & ditolak)
    public function approve(Request $request, User $user)
    {
        abort_if($user->isOwner(), 403);

        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'kasir'])],
        ], [
            'role.required' => 'Pilih role terlebih dahulu.',
            'role.in'       => 'Role tidak valid.',
        ]);

        $user->update(['role' => $data['role'], 'status' => 'approved']);

        return back()->with('success', "Akun {$user->username} aktif sebagai {$data['role']}.");
    }

    public function reject(User $user)
    {
        abort_if($user->isOwner(), 403);

        $user->update(['role' => null, 'status' => 'rejected']);

        return back()->with('success', "Akun {$user->username} ditolak.");
    }

    private function messages(): array
    {
        return [
            'name.required'        => 'Nama lengkap wajib diisi.',
            'username.required'    => 'Username wajib diisi.',
            'username.alpha_dash'  => 'Username hanya boleh huruf, angka, strip, dan underscore.',
            'username.min'         => 'Username minimal 4 karakter.',
            'username.unique'      => 'Username sudah dipakai.',
            'email.required'       => 'Email wajib diisi.',
            'email.email'          => 'Format email tidak valid.',
            'email.unique'         => 'Email sudah terdaftar.',
            'role.required'        => 'Role wajib dipilih.',
            'role.required_if'     => 'Role wajib dipilih untuk akun aktif.',
            'role.in'              => 'Role tidak valid.',
            'password.required'    => 'Password wajib diisi.',
            'password.min'         => 'Password minimal 8 karakter.',
        ];
    }
}