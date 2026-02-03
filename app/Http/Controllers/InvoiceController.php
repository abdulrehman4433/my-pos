<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Produk;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


use function Livewire\str;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('invoice.index');
    }

    public function data()
    {
        $invoices = Invoice::with('items.product')->orderBy('created_at', 'desc')->get();

        $data = [];
        $no   = 1;

        foreach ($invoices as $invoice) {
            $row = [];

            $row['no'] = $no++;
            $row['invoice_reference'] = $invoice->invoice_reference;
            $row['invoice_code'] = $invoice->invoice_code ?? 'N/A';
            $row['sub_total'] = number_format($invoice->sub_total, 2);
            $row['tax_amount'] = number_format($invoice->tax_amount, 2);
            $row['discount_amount'] = number_format($invoice->discount_amount, 2);
            $row['grand_total'] = number_format($invoice->grand_total, 2);
            $row['remaining_amount'] = number_format($invoice->remaining_amount, 2);
            $row['payment_received'] = (string) $invoice->payment_received;

            // payment status label
            if ($invoice->payment_status === 'paid') {
                $row['payment_status'] = '<span class="label label-success">Paid</span>';
            } elseif ($invoice->payment_status === 'partial') {
                $row['payment_status'] = '<span class="label label-warning">Partial</span>';
            } else {
                $row['payment_status'] = '<span class="label label-danger">Unpaid</span>';
            }

            // action buttons
            $row['action'] = '
                <button onclick="viewForm('. $invoice->id .')" 
                        class="btn btn-info btn-xs">
                    <i class="fa fa-eye"></i>
                </button>
                <button onclick="viewFormDownload('. $invoice->id .')" 
                        class="btn btn-warning btn-xs">
                    <i class="fa fa-file-pdf-o"></i>
                </button>             
            ';

            

            $data[] = $row;
        }

        return response()->json(['data' => $data]);
    }

    public function ProductData()
    {
        $products = Produk::orderBy('product_name')->get();
        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'selling_price' => $product->selling_price,
                'stock' => $product->stock,
            ];
        }
        return response()->json($data);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    
    // public function store(Request $request)
    // {
    //     // Base validation
    //     $request->validate([
    //         'invoice_code'       => ['required', 'string', 'max:255'],
    //         'invoice_reference'  => ['required', 'string', 'max:255'],
    //         'sub_total'          => ['required', 'numeric', 'min:0'],
    //         'tax_amount'         => ['nullable', 'numeric', 'min:0'],
    //         'discount_amount'    => ['nullable', 'numeric', 'min:0'],
    //         'payment_method'     => ['required', 'string', 'max:50'],
    //         'payment_status'     => ['required', 'string', Rule::in(['paid','unpaid','partial'])],
    //         // Products array - required only for product invoices
    //         'products'           => ['nullable', 'array'],
    //         'products.*.id'      => ['required_if:invoice_reference,product', 'integer'],
    //         'products.*.qty'     => ['required_if:invoice_reference,product', 'integer', 'min:1'],
    //         'products.*.price'   => ['nullable', 'numeric', 'min:0'],
    //         // For non-product invoices
    //         'reference_id'       => ['nullable', 'integer'], // Changed to integer
    //     ]);

    //     // Compute totals
    //     $subTotal       = (float) $request->input('sub_total', 0);
    //     $taxAmount      = (float) $request->input('tax_amount', 0);
    //     $discountRate   = (float) $request->input('discount_amount', 0); // percentage

    //     // Ensure discount is between 0 and 100
    //     $discountRate = min(max($discountRate, 0), 100);

    //     // Calculate discount value from percentage
    //     $discountValue = ($subTotal * $discountRate) / 100;

    //     // Final grand total
    //     $grandTotal = ($subTotal - $discountValue) + $taxAmount;

    //     // Optional: never allow negative grand total
    //     $grandTotal = max(0, $grandTotal);


    //     return DB::transaction(function () use ($request, $subTotal, $taxAmount, $discountRate, $grandTotal) {

    //         // Determine invoice resource and resource ID based on reference
    //         $invoiceReference = $request->input('invoice_reference');
    //         $referenceId = $request->input('reference_id');
            
    //         // Handle empty reference_id - set to null if empty string or not provided
    //         if ($referenceId === '' || $referenceId === null) {
    //             $referenceId = null;
    //         } else {
    //             // Convert to integer if it's a valid number
    //             $referenceId = (int) $referenceId;
    //         }
            
    //         // Map reference to resource type (you can adjust this mapping as needed)
    //         $resourceMapping = [
    //             'product' => 'product',
    //             'project' => 'project', 
    //             'maintenance' => 'maintenance',
    //             'rental' => 'rental',
    //             'other' => 'other',
    //             // Add other mappings as needed
    //         ];
            
    //         $invoiceResource = $resourceMapping[$invoiceReference] ?? $invoiceReference;
    //         $remaining_amount = 0.00;
    //         $received_amount = 0.00;
    //         if($request->input('payment_status') == 'partial'){
    //             $received_amount = (float) $request->input('received_amount', 0);
    //             $remaining_amount = $grandTotal - $received_amount;
    //         }

    //         // Create invoice
    //         $invoice = Invoice::create([
    //             'invoice_code'        => $request->input('invoice_code'),
    //             'invoice_reference'   => $invoiceReference,
    //             'invoice_resource'    => $invoiceResource,
    //             'invoice_resource_id' => $referenceId, // Now properly null or integer
    //             'sub_total'           => $subTotal,
    //             'tax_amount'          => $taxAmount,
    //             'discount_amount'     => $discountRate,
    //             'grand_total'         => $grandTotal,
    //             'payment_received'    => $request->input('payment_method'),
    //             'payment_status'      => $request->input('payment_status'),
    //             'received_amount'     => $received_amount,
    //             'remaining_amount'    => $remaining_amount,
    //             'created_by'          => auth()->id(),
    //             'updated_by'          => auth()->id(),
    //         ]);

    //         // IMPORTANT: For non-product invoices, ignore products array if provided
    //         if ($invoiceReference === 'product') {
    //             $products = $request->input('products', []);

    //             if (is_array($products) && count($products) > 0) {
    //                 foreach ($products as $single_product) {
    //                     $productId = $single_product['id'] ?? null;
    //                     $qty       = (int) ($single_product['qty'] ?? 0);
    //                     $price     = (float) ($single_product['price'] ?? 0);

    //                     if (!$productId || $qty < 1) {
    //                         continue;
    //                     }

    //                     $product = Produk::find($productId);
    //                     if (!$product) {
    //                         continue;
    //                     }

    //                     // Use provided price or fall back to product's selling_price
    //                     $unitPrice = $price > 0 ? $price : (float) ($product->selling_price);
    //                     $lineTotal = $unitPrice * $qty;

    //                     InvoiceItem::create([
    //                         'invoice_id'      => $invoice->id,
    //                         'item_id'        => $product->product_id,
    //                         'item_name'       => $product->product_name,
    //                         'per_item_price'  => $unitPrice,
    //                         'quantity'        => $qty,
    //                         'total_price'     => $lineTotal,
    //                     ]);
    //                 }
    //             }
    //         } else {
    //             // For non-product invoices (like project, maintenance, etc.)
    //             // Create a single invoice item with the reference as item name
    //             $itemName = ucfirst($invoiceReference) . " Invoice";
    //             $quantity = 1; // Default quantity for non-product invoices
    //             $perItemPrice = $subTotal; // Use sub_total as the price
                
    //             InvoiceItem::create([
    //                 'invoice_id'      => $invoice->id,
    //                 'item_name'       => $itemName,
    //                 'per_item_price'  => $perItemPrice,
    //                 'quantity'        => $quantity,
    //                 'total_price'     => $subTotal, // total_price equals sub_total for single item
    //             ]);
    //         }

    //         return response()->json([
    //             'success'     => true,
    //             'message'     => 'Invoice created successfully!',
    //             'invoice_id'  => $invoice->id,
    //             'grand_total' => $grandTotal,
    //         ]);
    //     }, 3);
    // }
    public function store(Request $request)
{
    $request->validate([
        'invoice_code'       => ['required', 'string', 'max:255'],
        'invoice_reference'  => ['required', 'string', 'max:255'],

        'sub_total'          => ['required', 'numeric', 'min:0'],
        'tax_amount'         => ['nullable', 'numeric', 'min:0'],
        'discount_amount'    => ['nullable', 'numeric', 'min:0', 'max:100'],

        'payment_method'     => ['required', 'string', 'max:50'],
        'payment_status'     => ['required', 'string', Rule::in(['paid','unpaid','partial'])],
        'received_amount'    => ['required_if:payment_status,partial', 'numeric', 'min:0'],

        'products'           => ['required_if:invoice_reference,product', 'array'],
        'products.*.id'      => ['required_if:invoice_reference,product', 'integer'], // id == product_id
        'products.*.qty'     => ['required_if:invoice_reference,product', 'integer', 'min:1'],
        'products.*.price'   => ['nullable', 'numeric', 'min:0'],

        'reference_id'       => ['nullable', 'integer'],
    ]);

    // totals
    $subTotal     = (float) $request->input('sub_total', 0);
    $taxAmount    = (float) $request->input('tax_amount', 0);
    $discountRate = (float) $request->input('discount_amount', 0);

    $discountRate  = min(max($discountRate, 0), 100);
    $discountValue = ($subTotal * $discountRate) / 100;
    $grandTotal    = max(0, ($subTotal - $discountValue) + $taxAmount);

    return DB::transaction(function () use ($request, $subTotal, $taxAmount, $discountRate, $grandTotal) {

        $invoiceReference = $request->input('invoice_reference');

        $referenceId = $request->input('reference_id');
        $referenceId = ($referenceId === '' || $referenceId === null) ? null : (int) $referenceId;

        $resourceMapping = [
            'product'      => 'product',
            'project'      => 'project',
            'maintenance'  => 'maintenance',
            'rental'       => 'rental',
            'other'        => 'other',
        ];
        $invoiceResource = $resourceMapping[$invoiceReference] ?? $invoiceReference;

        $received_amount  = 0.00;
        $remaining_amount = 0.00;
        if ($request->input('payment_status') === 'partial') {
            $received_amount  = (float) $request->input('received_amount', 0);
            $remaining_amount = max(0, $grandTotal - $received_amount);
        }

        // Create invoice
        $invoice = Invoice::create([
            'invoice_code'        => $request->input('invoice_code'),
            'invoice_reference'   => $invoiceReference,
            'invoice_resource'    => $invoiceResource,
            'invoice_resource_id' => $referenceId,

            'sub_total'           => $subTotal,
            'tax_amount'          => $taxAmount,
            'discount_amount'     => $discountRate,
            'grand_total'         => $grandTotal,

            'payment_received'    => $request->input('payment_method'),
            'payment_status'      => $request->input('payment_status'),
            'received_amount'     => $received_amount,
            'remaining_amount'    => $remaining_amount,

            'created_by'          => auth()->id(),
            'updated_by'          => auth()->id(),
        ]);

        // ==============================
        // PRODUCT BLOCK (MERGE + STOCK)
        // ==============================
        if ($invoiceReference === 'product') {

            $lines = collect($request->input('products', []))
                ->map(function ($row) {
                    return [
                        'product_id' => (int) ($row['id'] ?? 0),   // product_id
                        'qty'        => (int) ($row['qty'] ?? 0),
                        'price'      => (float) ($row['price'] ?? 0),
                    ];
                })
                ->filter(fn($r) => $r['product_id'] > 0 && $r['qty'] > 0)
                ->values();

            if ($lines->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'products' => 'No valid products found in request.',
                ]);
            }

            // Load products in one query
            $productIds = $lines->pluck('product_id')->unique()->values()->all();
            $produkMap  = Produk::whereIn('product_id', $productIds)->get()->keyBy('product_id');

            // Validate products exist
            foreach ($productIds as $pid) {
                if (!$produkMap->has($pid)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'products' => "Product not found for product_id: {$pid}",
                    ]);
                }
            }

            /**
             * ✅ Merge duplicates into ONE row per product_id
             * Also enforce SAME price per product_id (otherwise throw).
             * (If you want weighted avg instead, tell me.)
             */
            $merged = [];
            foreach ($lines as $idx => $l) {
                $pid = $l['product_id'];
                $produk = $produkMap[$pid];

                $unitPrice = $l['price'] > 0 ? $l['price'] : (float) $produk->selling_price;

                if (!isset($merged[$pid])) {
                    $merged[$pid] = [
                        'product'     => $produk,
                        'product_id'  => $pid,
                        'qty'         => 0,
                        'unit_price'  => $unitPrice,
                    ];
                }

                // If same product repeated but different price -> error (prevents wrong totals)
                if (abs($merged[$pid]['unit_price'] - $unitPrice) > 0.00001) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "products" => "Same product_id {$pid} has different prices. Use same price or change rule to weighted average.",
                    ]);
                }

                $merged[$pid]['qty'] += (int) $l['qty'];
            }

            /**
             * ✅ Lock stock rows FOR UPDATE
             */
            $stockRows = \App\Models\ProductStock::whereIn('product_id', array_keys($merged))
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            // Validate stock record exists + sufficient
            foreach ($merged as $pid => $m) {
                $stock = $stockRows->get($pid);

                if (!$stock) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock' => "Stock record missing for product_id: {$pid}",
                    ]);
                }

                if ((int) $stock->stock < (int) $m['qty']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock' => "Insufficient stock for product_id {$pid}. Available: {$stock->stock}, Required: {$m['qty']}",
                    ]);
                }
            }

            /**
             * ✅ Atomic stock decrement with protection (won’t go negative)
             * We also update updated_by.
             */
            $userId = auth()->id();
            foreach ($merged as $pid => $m) {
                $qty = (int) $m['qty'];

                $updated = \App\Models\ProductStock::where('product_id', $pid)
                    ->where('stock', '>=', $qty)
                    ->update([
                        'stock'      => DB::raw("stock - {$qty}"),
                        'updated_by' => $userId,
                    ]);

                if ($updated === 0) {
                    // In case of concurrency or unexpected changes
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock' => "Failed to deduct stock for product_id {$pid}. Please retry.",
                    ]);
                }
            }

            /**
             * ✅ Insert ONE invoice item per product_id
             */
            foreach ($merged as $pid => $m) {
                $produk = $m['product'];
                $qty    = (int) $m['qty'];
                $price  = (float) $m['unit_price'];

                InvoiceItem::create([
                    'invoice_id'     => $invoice->id,
                    'item_id'        => $produk->product_id,
                    'item_name'      => $produk->product_name,
                    'per_item_price' => $price,
                    'quantity'       => $qty,
                    'total_price'    => $qty * $price,
                ]);
            }

        } else {
            // Non-product invoice item
            $itemName     = ucfirst($invoiceReference) . " Invoice";
            $quantity     = 1;
            $perItemPrice = $subTotal;

            InvoiceItem::create([
                'invoice_id'     => $invoice->id,
                'item_name'      => $itemName,
                'per_item_price' => $perItemPrice,
                'quantity'       => $quantity,
                'total_price'    => $subTotal,
            ]);
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Invoice created successfully!',
            'invoice_id'  => $invoice->id,
            'grand_total' => $grandTotal,
        ]);

    }, 3);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // app/Http/Controllers/InvoiceController.php
    public function view($id)
    {
        // Fetch the invoice
        $invoice = Invoice::with('items')->findOrFail($id);

        // Pass to Blade view
        return view('invoice.view', compact('invoice'));
    }

    public function CustomerData()
    {
        $customers = Customer::orderBy('name')->get();
        $data = [];
        foreach ($customers as $customer) {
            $data[] = [
                'id' => $customer->id,
                'name' => $customer->name,
                'customer_code' => $customer->customer_code,
                'discount' => $customer->discount,
            ];
        }
        return response()->json($data);

    }
}
