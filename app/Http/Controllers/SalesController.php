<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SalesImport;
use Workdo\Pos\Entities\Pos;
use Illuminate\Support\Collection;

class SalesController extends Controller
{
    public function index()
    {
        $mergedSalesData = new Collection();

        // 1. Get existing sales data from the database
        $dbSales = Pos::all();
        foreach ($dbSales as $sale) {
            $mergedSalesData->push([
                'ID' => $sale->pos_id,
                'Date' => $sale->pos_date ? $sale->pos_date->format('Y-m-d') : '',
                'Status' => $sale->status,
                'Gross Amount' => $sale->gross_amount,
                'Net Amount' => $sale->net_amount,
                'Source' => 'Database',
            ]);
        }

        // 2. Get extracted data from session (from Excel upload)
        $extractedData = session('extracted_sales_data');
        if ($extractedData && $extractedData->count() > 0) {
            foreach ($extractedData as $row) {
                $mergedSalesData->push([
                    'ID' => $row['order_id'] ?? 'N/A',
                    'Date' => $row['date_time'] ?? 'N/A',
                    'Status' => $row['status'] ?? 'N/A',
                    'Gross Amount' => $row['gross_amount'] ?? 'N/A',
                    'Net Amount' => $row['net_amount'] ?? 'N/A',
                    'Source' => 'Excel',
                ]);
            }
        }

        // Clear the session data after displaying
        session()->forget('extracted_sales_data');

        return view('sales.index', compact('mergedSalesData'));
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'sales_file' => 'required|mimes:xlsx,xls,csv'
            ]);

            $collection = Excel::toCollection(new SalesImport, $request->file('sales_file'));
            session()->flash('extracted_sales_data', $collection[0]); // Assuming the first sheet

            return redirect()->route('sales.index')->with('success', __('Sales data extracted successfully. Review before saving.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', __('Validation Error: ') . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Error extracting sales data: ') . $e->getMessage());
        }
    }
}
