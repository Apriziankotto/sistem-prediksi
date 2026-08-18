<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $users = User::with('role')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('role', function ($roleQuery) use ($search) {
                          $roleQuery->where('nama_role', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('id', 'asc')->get();

        $totalUser = User::count();
        $totalRole = Role::count();
        $totalAdmin = User::whereHas('role', function ($query) {
            $query->where('nama_role', 'Super Admin');
        })->count();

        return view('users.index', compact(
            'users',
            'roles',
            'totalUser',
            'totalRole',
            'totalAdmin'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email user wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'role_id.required' => 'Role user wajib dipilih.',
            'role_id.exists' => 'Role yang dipilih tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'User baru berhasil ditambahkan.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Akun yang sedang digunakan tidak boleh dihapus.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}