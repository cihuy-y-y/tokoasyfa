<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $pending  = User::where('status', 'pending')->latest()->get();
        $approved = User::where('status', 'approved')->where('role', '!=', 'owner')->latest()->get();
        $rejected = User::where('status', 'rejected')->latest()->get();

        return view('owner.users', compact('pending', 'approved', 'rejected'));
    }

    // Setujui + tentukan role (juga dipakai untuk mengubah role)
    public function approve(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:admin,kasir'],
        ], [
            'role.required' => 'Pilih role terlebih dahulu.',
            'role.in'       => 'Role tidak valid.',
        ]);

        if ($user->isOwner()) {
            return back()->with('error', 'Akun owner tidak bisa diubah.');
        }

        $user->update([
            'role'   => $data['role'],
            'status' => 'approved',
        ]);

        return back()->with('success', "Akun {$user->username} aktif sebagai {$data['role']}.");
    }

    public function reject(User $user)
    {
        if ($user->isOwner()) {
            return back()->with('error', 'Akun owner tidak bisa diubah.');
        }

        $user->update(['role' => null, 'status' => 'rejected']);

        return back()->with('success', "Akun {$user->username} ditolak.");
    }

    // Nonaktifkan akun yang sudah aktif (kembali ke pending)
    public function deactivate(User $user)
    {
        if ($user->isOwner()) {
            return back()->with('error', 'Akun owner tidak bisa diubah.');
        }

        $user->update(['role' => null, 'status' => 'pending']);

        return back()->with('success', "Akun {$user->username} dinonaktifkan.");
    }
}