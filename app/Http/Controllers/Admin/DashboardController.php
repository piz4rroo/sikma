<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Menu;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard admin.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $totalOrders = Order::count();
        $dailyRevenue = Order::whereDate('created_at', today())->sum('total_amount');
        $popularMenus = Menu::withCount('orderItems')->orderBy('order_items_count', 'desc')->take(5)->get();
        $latestOrders = Order::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('totalOrders', 'dailyRevenue', 'popularMenus', 'latestOrders'));
    }
}
