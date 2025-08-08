<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Workdo\Pos\Entities\Pos;
use Workdo\Pos\Entities\PosProduct;
use Workdo\ProductService\Entities\ProductService;
use Workdo\ProductService\Entities\Category;
use App\Models\User;
use App\Models\Warehouse;

class SalesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // Assuming a default customer and warehouse for imported sales
        // You might want to make these configurable or derive them from the Excel data
        $defaultCustomerId = User::where('type', 'customer')->first()->id ?? 1; // Fallback to user ID 1 if no customer found
        if (isset($row['customer_name'])) {
            $customer = User::where('name', trim($row['customer_name']))->where('type', 'customer')->first();
            $defaultCustomerId = $customer->id ?? $defaultCustomerId;
        }
        $defaultWarehouseId = Warehouse::first()->id ?? 1; // Fallback to warehouse ID 1

        foreach ($rows as $row) {
            // Create Pos entry
            $pos = Pos::create([
                'pos_id' => $row['order_id'],
                'customer_id' => $defaultCustomerId,
                'warehouse_id' => $defaultWarehouseId,
                'pos_date' => \Carbon\Carbon::parse($row['date_time']),
                'status' => $row['status'],
                'order_type' => $row['order_type'], // New field
                'gross_amount' => $row['gross_amount'], // New field
                'platform_fee' => $row['platform_fee'], // New field
                'net_amount' => $row['net_amount'], // New field
                'delivery_time' => $row['delivery_time'], // New field
                'created_by' => auth()->id(),
                'workspace' => getActiveWorkSpace(),
            ]);

            // Handle Items Ordered
            $itemsOrdered = explode(',', $row['items_ordered']);
            $quantities = explode(',', $row['qty']);

            foreach ($itemsOrdered as $key => $itemName) {
                $product = ProductService::where('name', trim($itemName))->first();

                if ($product) {
                    PosProduct::create([
                        'pos_id' => $pos->id,
                        'product_id' => $product->id,
                        'quantity' => $quantities[$key] ?? 0,
                        'price' => $product->sale_price, // Using sale_price
                        'tax' => $product->tax_id, // Assuming tax_id for now
                        'discount' => 0, // Assuming no discount from Excel
                        'created_by' => auth()->id(),
                        'workspace' => getActiveWorkSpace(),
                    ]);
                }
            }
        }
    }
}
