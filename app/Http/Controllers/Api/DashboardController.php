<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\StockImport;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummary(Request $request)
    {
        $today = Carbon::today();

        // Total Sales (all time)
        $totalSales = Order::sum('total_amount');

        // Total Profit (all time)
        $totalProfit = Order::sum('total_profit');

        // Total Products
        $totalProducts = Product::count();

        // New Users Today (ລູກຄ້າໃໝ່ ຫຼື ຜູ້ໃຊ້ໃໝ່)
        $newCustomers = User::whereDate('created_at', $today)->count();

        return response()->json([
            'totalSales' => $totalSales,
            'totalProducts' => $totalProducts,
            'newCustomers' => $newCustomers,
            'totalProfit' => $totalProfit,
        ]);
    }

    /**
     * Get recent activities for the dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivities(Request $request)
    {
        $activities = collect();

        // Fetch recent sales
        $recentSales = Order::with(['items.product', 'seller'])
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => 'sale-' . $order->id,
                    'type' => 'sale',
                    'description' => 'ຂາຍສິນຄ້າ: ' . ($order->items->first()->product->name ?? 'ສິນຄ້າຫຼາຍລາຍການ'), // Simplified, assumes order has items
                    'time' => Carbon::parse($order->order_date)->diffForHumans(),
                    'value' => '₭ ' . number_format($order->total_amount, 0),
                    'timestamp' => $order->order_date,
                ];
            });
        $activities = $activities->merge($recentSales);

        // Fetch recent stock imports
        $recentStockImports = StockImport::with('product') // Eager load product for description
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($stockImport) {
                return [
                    'id' => 'stock_in-' . $stockImport->id,
                    'type' => 'stock_in',
                    'description' => 'ນຳເຂົ້າສິນຄ້າ: ' . ($stockImport->product->name ?? 'ບໍ່ລະບຸ'),
                    'time' => Carbon::parse($stockImport->created_at)->diffForHumans(),
                    'value' => '+' . $stockImport->quantity . ' ໜ່ວຍ',
                    'timestamp' => $stockImport->created_at,
                ];
            });
        $activities = $activities->merge($recentStockImports);

        // Fetch recent new users (customers)
        $newUsers = User::orderBy('created_at', 'desc')
            ->limit(3) // Fewer new users to keep activities diverse
            ->get()
            ->map(function ($user) {
                return [
                    'id' => 'new_customer-' . $user->id,
                    'type' => 'new_customer',
                    'description' => 'ຜູ້ໃຊ້ໃໝ່: ' . $user->name,
                    'time' => Carbon::parse($user->created_at)->diffForHumans(),
                    'value' => 'ໃໝ່',
                    'timestamp' => $user->created_at,
                ];
            });
        $activities = $activities->merge($newUsers);

        // Sort all activities by timestamp and take the latest 10
        $finalActivities = $activities->sortByDesc('timestamp')->take(5)->values();

        return response()->json($finalActivities);
    }
}
