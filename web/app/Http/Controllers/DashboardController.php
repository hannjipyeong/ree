<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SubTask;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalSupir = User::where('role', 'supir')->count();
        $totalSubTasks = SubTask::count();

        $recentOrders = Order::with(['customer', 'subTasks', 'containers'])
            ->latest()
            ->take(5)
            ->get();

        $subTaskStats = [
            'masuk' => SubTask::where('status', 'Masuk')->count(),
            'in' => SubTask::where('status', 'In')->count(),
            'out' => SubTask::where('status', 'Out')->count(),
            'done' => SubTask::where('status', 'Done')->count(),
        ];

        return view('dashboard', compact(
            'totalOrders',
            'totalCustomers',
            'totalSupir',
            'totalSubTasks',
            'recentOrders',
            'subTaskStats'
        ));
    }
}
