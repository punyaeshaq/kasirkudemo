<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Check if current user is admin or superadmin
     */
    private function ensureAdmin()
    {
        $user = auth()->user();
        if (!$user || (!$user->isSuperAdmin() && !$user->isAdmin())) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat mengakses fitur ini.');
        }
    }

    /**
     * Check if current user is superadmin
     */
    private function ensureSuperAdmin()
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        $currentUser = auth()->user();

        // Superadmin can see all users
        // Admin can only see admin and kasir (not superadmin)
        if ($currentUser->isSuperAdmin()) {
            $users = User::orderBy('name')->get();
        } else {
            $users = User::where('role', '!=', 'superadmin')->orderBy('name')->get();
        }

        return response()->json(['data' => $users]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $currentUser = auth()->user();
        $allowedRoles = $currentUser->isSuperAdmin()
            ? ['superadmin', 'admin', 'kasir']
            : ['admin', 'kasir'];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'is_active' => 'boolean',
            'permissions' => 'nullable|array'
        ]);

        // Admin cannot create superadmin
        if (!$currentUser->isSuperAdmin() && $request->role === 'superadmin') {
            return response()->json(['message' => 'Anda tidak dapat membuat akun superadmin'], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json(['data' => $user], 201);
    }

    public function show(User $user)
    {
        $this->ensureAdmin();

        $currentUser = auth()->user();

        // Admin cannot view superadmin
        if (!$currentUser->isSuperAdmin() && $user->isSuperAdmin()) {
            abort(403, 'Anda tidak dapat mengakses data superadmin');
        }

        return response()->json(['data' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();

        $currentUser = auth()->user();

        // Admin cannot edit superadmin
        if (!$currentUser->isSuperAdmin() && $user->isSuperAdmin()) {
            return response()->json(['message' => 'Anda tidak dapat mengedit akun superadmin'], 403);
        }

        $allowedRoles = $currentUser->isSuperAdmin()
            ? ['superadmin', 'admin', 'kasir']
            : ['admin', 'kasir'];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'is_active' => 'boolean',
            'permissions' => 'nullable|array'
        ]);

        // Admin cannot promote to superadmin
        if (!$currentUser->isSuperAdmin() && $request->role === 'superadmin') {
            return response()->json(['message' => 'Anda tidak dapat mengubah role menjadi superadmin'], 403);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->is_active ?? true
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['data' => $user]);
    }

    public function destroy(User $user)
    {
        $this->ensureAdmin();

        $currentUser = auth()->user();

        // Prevent deleting self
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri'], 422);
        }

        // Admin cannot delete superadmin
        if (!$currentUser->isSuperAdmin() && $user->isSuperAdmin()) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun superadmin'], 403);
        }

        $user->delete();
        return response()->json(null, 204);
    }

    /**
     * Get available permissions list
     */
    public function permissions()
    {
        return response()->json([
            'data' => User::AVAILABLE_PERMISSIONS
        ]);
    }
}

