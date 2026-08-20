<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'admin_source',
        'supir_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin' && empty($this->admin_source);
    }

    public function isAdminSource(string $source): bool
    {
        return $this->role === 'admin' && strcasecmp((string)$this->admin_source, $source) === 0;
    }

    /**
     * Cek apakah user memiliki hak akses ke order dengan source tertentu.
     * Super Admin memiliki akses ke semua source.
     * Admin scoped hanya memiliki akses ke source miliknya.
     */
    public function hasSourceAccess(?string $source): bool
    {
        if (!$this->isAdmin()) {
            return false;
        }

        // Super Admin has access to everything
        if (empty($this->admin_source)) {
            return true;
        }

        if (empty($source)) {
            return false;
        }

        return strcasecmp(trim($this->admin_source), trim($source)) === 0;
    }

    /**
     * Label role yang diformat ramah pengguna
     */
    public function getRoleTitleAttribute(): string
    {
        if ($this->isAdmin()) {
            if ($this->admin_source) {
                return 'Admin ' . $this->admin_source;
            }
            return 'Super Administrator';
        }

        if ($this->isSupir()) {
            if (in_array(strtolower((string)$this->supir_type), ['haulage', 'houlage'])) {
                return 'Supir ' . ($this->supir_type ?: 'Haulage');
            }
            return 'Pelaksana Lapangan ' . ($this->supir_type ?: 'Operasional');
        }

        return 'Customer';
    }

    public function isSupir(): bool
    {
        return $this->role === 'supir';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
