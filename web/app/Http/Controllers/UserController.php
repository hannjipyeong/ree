<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(10);
        return view('customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        $customer->load(['orders.containers', 'orders.subTasks.supir']);
        $totalOrders = $customer->orders()->count();
        $doneOrders = $customer->orders()->whereIn('status', ['Done', 'DONE', 'Selesai', 'completed'])->count();
        $inProgressOrders = $customer->orders()->whereIn('status', ['In Progress', 'In', 'Out', 'in', 'out'])->count();
        $pendingOrders = $customer->orders()->whereIn('status', ['Submitted', 'Masuk', 'Pending', 'masuk'])->count();

        $orders = $customer->orders()->with(['containers', 'subTasks.supir'])->latest()->paginate(15);

        return view('customers.show', compact('customer', 'totalOrders', 'doneOrders', 'inProgressOrders', 'pendingOrders', 'orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:6',
            'default_nama_pt' => 'nullable|string|max:255',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'default_nama_pt' => $validated['default_nama_pt'] ?? null,
            'has_default_asuransi' => $request->has('has_default_asuransi'),
        ]);

        return redirect()->route('customers.index')->with('success', 'Akun Customer berhasil ditambahkan!');
    }

    public function update(Request $request, User $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => 'required|string',
            'password' => 'nullable|string|min:6',
            'default_nama_pt' => 'nullable|string|max:255',
        ]);

        $customer->name = $validated['name'];
        $customer->email = $validated['email'];
        $customer->phone = $validated['phone'];
        $customer->default_nama_pt = $validated['default_nama_pt'] ?? null;
        $customer->has_default_asuransi = $request->has('has_default_asuransi');
        
        if (!empty($validated['password'])) {
            $customer->password = Hash::make($validated['password']);
        }
        $customer->save();

        return redirect()->route('customers.index')->with('success', 'Akun Customer berhasil diperbarui!');
    }

    public function destroy(User $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Akun Customer berhasil dihapus.');
    }
}
