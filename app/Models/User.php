<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Available permissions for kasir (superadmin-only features excluded)
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

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
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
        // Super Admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin has all permissions except superadmin-only ones
        if ($this->isAdmin()) {
            // These are superadmin-only permissions
            $superadminOnly = ['users', 'activations'];
            return !in_array($permission, $superadminOnly);
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
        if ($this->isSuperAdmin()) {
            return array_keys(self::AVAILABLE_PERMISSIONS);
        }

        if ($this->isAdmin()) {
            // Admin gets all except superadmin-only
            $superadminOnly = ['users', 'activations'];
            return array_filter(array_keys(self::AVAILABLE_PERMISSIONS), function ($perm) use ($superadminOnly) {
                return !in_array($perm, $superadminOnly);
            });
        }

        return $this->permissions ?? [];
    }
}

