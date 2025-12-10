<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Store a newly created order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'amount_received' => 'required|numeric|min:0',
            'change_amount' => 'required|numeric|min:0',
        ]);

        try {
            $totalAmount = 0;
            $totalProfit = 0; // Initialize totalProfit
            $orderItemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check for sufficient stock
                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'message' => 'ສິນຄ້າບໍ່ພຽງພໍໃນສະຕັອກ.',
                        'product_name' => $product->name,
                        'available_stock' => $product->stock_quantity,
                    ], 422); // Unprocessable Entity
                }

                $sellingPriceAtSale = $product->selling_price;
                $costPriceAtSale = $product->cost_price; // Retrieve cost_price
                $itemTotal = $sellingPriceAtSale * $item['quantity'];
                $itemProfit = ($sellingPriceAtSale - $costPriceAtSale) * $item['quantity']; // Calculate item profit
                
                $totalAmount += $itemTotal;
                $totalProfit += $itemProfit; // Accumulate total profit

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $sellingPriceAtSale,
                    'cost_at_sale' => $costPriceAtSale, // Store cost_price
                    'item_total' => $itemTotal,
                    'item_profit' => $itemProfit, // Store item profit
                ];

                // Decrement stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // Create the Order
            $order = Order::create([
                'order_code' => 'ORD-' . time(), // Simple unique code
                'total_amount' => $totalAmount,
                'seller_user_id' => Auth::id(),
                'amount_received' => $validated['amount_received'],
                'change_amount' => $validated['change_amount'],
                'total_profit' => $totalProfit, // Store the calculated total profit
                'order_date' => now(), // Set the current date and time for order_date
            ]);

            // Create Order Items
            $order->items()->createMany($orderItemsData);

            // Eager load the created order with details for the receipt
            $order->load('items.product', 'seller');

            return response()->json($order, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'ເກີດຂໍ້ຜິດພາດໃນການສ້າງລາຍການສັ່ງຊື້.', 'error' => $e->getMessage()], 500);
        }
    }
}
