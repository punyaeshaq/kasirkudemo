<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Available permissions
    public const AVAILABLE_PERMISSIONS = [
        'dashboard' => 'Dashboard',
        'pos' => 'Kasir (POS)',
        'products' => 'Produk',
        'categories' => 'Kategori',
        'transactions' => 'Transaksi',
        'customers' => 'Pelanggan',
        'debts' => 'Piutang',
        'bank_accounts' => 'Rekening & E-Wallet',
        'discounts' => 'Manajemen Diskon',
        'reports' => 'Laporan',
        'users' => 'Manajemen Pengguna',
        'backup' => 'Backup Database',
        'settings' => 'Pengaturan',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
        'login_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    /**
     * Check if user has permission for a specific feature
     */
    public function hasPermission(string $permission): bool
    {
        // Admin has all permissions
        if ($this->isAdmin()) {
            return true;
        }

        // If no permissions set, deny access
        if (empty($this->permissions)) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Get all permissions for non-admin users
     */
    public function getActivePermissions(): array
    {
        if ($this->isAdmin()) {
            return array_keys(self::AVAILABLE_PERMISSIONS);
        }

        return $this->permissions ?? [];
    }
}

