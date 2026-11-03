<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{

    // Semua user kecuali owner (pending/rejected punya role null, jadi harus ikut)
    public function scopeBukanOwner($query)
    {
        return $query->where(function ($w) {
            $w->whereNull('role')->orWhere('role', '!=', 'owner');
        });
    }

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime', 
        ];
    }

    public function isOwner(): bool    { return $this->role === 'owner'; }
    public function isApproved(): bool { return $this->status === 'approved'; }

    // Tujuan halaman setelah login, sesuai role
    public function homeRoute(): string
    {
        return match ($this->role) {
            'owner' => 'owner.users',
            'admin' => 'admin.dashboard',
            'kasir' => 'pos.index',
            default => 'login',
        };
    }
}