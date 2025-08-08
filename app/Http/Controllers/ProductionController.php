<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Workdo\ProductService\Entities\Category;
use Workdo\ProductService\Entities\ProductService;
use Workdo\Pos\Entities\Pos;
use Workdo\Pos\Entities\PosProduct;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;

class ProductionController extends Controller
{
    public function index()
    {
        $purchaseCategories = Category::where('type', 2)->pluck('name', 'id');
        $itemCategories = Category::where('type', 0)->pluck('name', 'id');
        $productServices = ProductService::all();

        return view('production.index', compact('purchaseCategories', 'itemCategories', 'productServices'));
    }

    public function storeProduction(Request $request)
    {
        // Validate the request data
        $request->validate([
            'purchase_category' => 'required|exists:categories,id',
            'item_category' => 'required|exists:categories,id',
            'usage_quantity' => 'required|numeric|min:0',
            'product_name.*' => 'required|exists:product_services,id',
            'quantity.*' => 'required|numeric|min:1',
        ]);

        try {
            // Get default customer and warehouse
            $defaultCustomerId = Auth::id(); // Using current user as customer for internal production
            $defaultWarehouseId = Warehouse::first()->id ?? 1; // Fallback to warehouse ID 1

            // Create Pos entry
            $pos = Pos::create([
                'pos_id' => Pos::posNumberFormat(Pos::nextPosId()), // Assuming nextPosId() exists or similar logic
                'customer_id' => $defaultCustomerId,
                'warehouse_id' => $defaultWarehouseId,
                'pos_date' => now(),
                'category_id' => $request->item_category, // Item Category of produced items
                'status' => 'Completed',
                'order_type' => 'Production',
                'gross_amount' => 0, // Not directly mapped from production input
                'platform_fee' => 0, // Not directly mapped from production input
                'net_amount' => 0, // Not directly mapped from production input
                'delivery_time' => null, // Not directly mapped from production input
                'raw_material_purchase_category_id' => $request->purchase_category,
                'raw_material_item_category_id' => $request->item_category,
                'raw_material_usage_quantity' => $request->usage_quantity,
                'created_by' => Auth::id(),
                'workspace' => getActiveWorkSpace(),
            ]);

            // Create PosProduct entries for each produced item
            foreach ($request->product_name as $key => $productId) {
                $productService = ProductService::find($productId);
                if ($productService) {
                    PosProduct::create([
                        'pos_id' => $pos->id,
                        'product_id' => $productId,
                        'quantity' => $request->quantity[$key],
                        'price' => $productService->sale_price, // Use sale_price for produced items
                        'tax' => $productService->tax_id, // Assuming tax_id from product service
                        'discount' => 0,
                        'total' => ($request->quantity[$key] * $productService->sale_price), // Simple total
                        'created_by' => Auth::id(),
                        'workspace' => getActiveWorkSpace(),
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => __('Production recorded successfully.')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('Error recording production: ') . $e->getMessage()], 500);
        }
    }
}
