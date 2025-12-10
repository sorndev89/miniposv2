<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockImportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'import_date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $search = $request->input('search', '');

        $query = StockImport::with(['product.category', 'product.unit', 'importer'])
            ->whereHas('product', function ($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                }
            });

        return $query->orderBy($sortBy, $sortDirection)->paginate($perPage);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        try {
            DB::beginTransaction();

            $stockImport = StockImport::create([
                'import_date' => now(),
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'cost_price' => $validated['cost_price'],
                'total_cost' => $validated['quantity'] * $validated['cost_price'],
                'importer_user_id' => Auth::id(),
                'description' => $validated['description'],
            ]);

            // Update product stock and selling price
            $product->stock_quantity += $validated['quantity'];
            if (isset($validated['selling_price'])) {
                $product->selling_price = $validated['selling_price'];
            }
            $product->save();

            DB::commit();

            return response()->json($stockImport->load(['product.category', 'product.unit', 'importer']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to import stock', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockImport $stockImport)
    {
        return $stockImport->load(['product.category', 'product.unit', 'importer']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockImport $stockImport)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product = Product::findOrFail($stockImport->product_id);
        
        try {
            DB::beginTransaction();

            // Revert old stock quantity
            $product->stock_quantity -= $stockImport->quantity;

            // Update StockImport
            $stockImport->update([
                'quantity' => $validated['quantity'],
                'cost_price' => $validated['cost_price'],
                'total_cost' => $validated['quantity'] * $validated['cost_price'],
                'description' => $validated['description'],
            ]);

            // Apply new stock quantity and price
            $product->stock_quantity += $validated['quantity'];
             if (isset($validated['selling_price'])) {
                $product->selling_price = $validated['selling_price'];
            }
            $product->save();

            DB::commit();

            return response()->json($stockImport->load(['product.category', 'product.unit', 'importer']));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update import', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockImport $stockImport)
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($stockImport->product_id);
            
            // Revert stock quantity
            if ($product->stock_quantity >= $stockImport->quantity) {
                 $product->stock_quantity -= $stockImport->quantity;
                 $product->save();
            } else {
                throw new \Exception('Cannot remove import, not enough stock to revert.');
            }

            $stockImport->delete();

            DB::commit();
            
            return response()->json(null, 204);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete import', 'error' => $e->getMessage()], 500);
        }
    }
}
