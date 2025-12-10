<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
// use PDF; // Import the PDF facade
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Get sales reports within a specified date range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesReports(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $orders = Order::with(['items.product', 'seller'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date', 'desc')
            ->get();

        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $totalProfit = $orders->sum('total_profit'); // Assuming total_profit is now correctly stored

        $formattedReports = $orders->map(function ($order) {
            return [
                'order_id' => $order->order_code,
                'seller' => $order->seller->name ?? 'N/A',
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->product->name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'price' => $item->price_at_sale,
                        'cost' => $item->cost_at_sale, // Assuming cost_at_sale is available
                    ];
                }),
                'total_amount' => $order->total_amount,
                'amount_received' => $order->amount_received,
                'change_amount' => $order->change_amount,
                'profit' => $order->total_profit,
                'sale_date' => Carbon::parse($order->order_date)->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'summary' => [
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'total_profit' => $totalProfit,
            ],
            'reports' => $formattedReports,
        ]);
    }

    /**
     * Export sales reports as PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportSalesPdf(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $orders = Order::with(['items.product', 'seller'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date', 'desc')
            ->get();

        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $totalProfit = $orders->sum('total_profit');

        $formattedReports = $orders->map(function ($order) {
            return [
                'order_id' => $order->order_code,
                'seller' => $order->seller->name ?? 'N/A',
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->product->name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'price' => $item->price_at_sale,
                        'cost' => $item->cost_at_sale,
                    ];
                }),
                'total_amount' => $order->total_amount,
                'amount_received' => $order->amount_received,
                'change_amount' => $order->change_amount,
                'profit' => $order->total_profit,
                'sale_date' => Carbon::parse($order->order_date)->format('Y-m-d H:i:s'),
            ];
        });

        $summary = [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_profit' => $totalProfit,
        ];

        $data = [
            'startDate' => $startDate->format('d/m/Y'),
            'endDate' => $endDate->format('d/m/Y'),
            'summary' => $summary,
            'reports' => $formattedReports,
        ];

      
        $pdf = PDF::loadView('sales_report_pdf', $data);

        return $pdf->download('sales_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.pdf');
    }
}
