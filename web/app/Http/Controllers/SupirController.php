<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SupirController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'supir');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('supir_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('supir_type')) {
            $query->where('supir_type', $request->supir_type);
        }

        $supirs = $query->latest()->paginate(10);
        return view('supir.index', compact('supirs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:6',
            'supir_type' => 'required|in:Haulage,LOLO,Penumpukan,TKBM',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'supir',
            'supir_type' => $validated['supir_type'],
        ]);

        return redirect()->route('supir.index')->with('success', 'Akun Pelaksana Lapangan berhasil ditambahkan!');
    }

    public function update(Request $request, User $supir)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $supir->id,
            'phone' => 'required|string',
            'password' => 'nullable|string|min:6',
            'supir_type' => 'required|in:Haulage,LOLO,Penumpukan,TKBM',
        ]);

        $supir->name = $validated['name'];
        $supir->email = $validated['email'];
        $supir->phone = $validated['phone'];
        $supir->supir_type = $validated['supir_type'];
        if (!empty($validated['password'])) {
            $supir->password = Hash::make($validated['password']);
        }
        $supir->save();

        return redirect()->route('supir.index')->with('success', 'Akun Pelaksana Lapangan berhasil diperbarui!');
    }

    public function destroy(User $supir)
    {
        $supir->delete();
        return redirect()->route('supir.index')->with('success', 'Akun Pelaksana Lapangan berhasil dihapus.');
    }
}
