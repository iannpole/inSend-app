<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Order;
use App\Models\User;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Product::count(),
            'recipes'  => Recipe::count(),
            'orders'   => Order::count(),
            'users'    => User::count(),
        ];

        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Activity Logs
        $activities = \App\Models\ActivityLog::orderBy('created_at', 'desc')->take(10)->get();

        // Order Progress
        $totalOrders = $stats['orders'] ?: 1; // avoid division by zero
        $completedOrders = Order::where('status', Order::STATUS_DELIVERED)->count();
        $inProgressOrders = Order::whereIn('status', [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED])->count();
        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();

        $orderProgress = [
            'completed' => round(($completedOrders / $totalOrders) * 100),
            'in_progress' => round(($inProgressOrders / $totalOrders) * 100),
            'pending' => round(($pendingOrders / $totalOrders) * 100),
            'completed_count' => $completedOrders
        ];

        // Sales Analytics (Last 7 Days)
        $salesAnalytics = [];
        $maxSales = 0;
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            
            $dailySales = Order::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('total_price') ?? 0;
            if ($dailySales > $maxSales) $maxSales = $dailySales;
            
            $salesAnalytics[] = [
                'day' => $date->format('D'), // Mon, Tue, etc.
                'total' => $dailySales,
            ];
        }

        return view('admin.dashboard', compact('stats', 'recentOrders', 'activities', 'orderProgress', 'salesAnalytics', 'maxSales'));
    }

    public function exportOrders(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $fileName = 'orders_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        
        return Excel::download(new OrdersExport($request->start_date, $request->end_date), $fileName);
    }
}
