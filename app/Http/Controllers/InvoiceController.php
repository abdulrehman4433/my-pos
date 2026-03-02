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
        $invoices = Invoice::whereNotIn('return_status', ['full'])->with('items.product')->orderBy('created_at', 'desc')->get();

        $data = [];
        $no   = 1;

        foreach ($invoices as $invoice) {
            $row = [];

            $row['no'] = $no++;
            $row['invoice_reference'] = $invoice->invoice_reference;
            $row['invoice_code'] = $invoice->invoice_code ?? 'N/A';
            $row['sub_total'] = $invoice->sub_total;
            $row['tax_amount'] = $invoice->tax_amount;
            $row['discount_amount'] = $invoice->discount_amount . '%';
            $row['grand_total'] = $invoice->grand_total;
            $row['remaining_amount'] = $invoice->grand_total - $invoice->received_amount;
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
                <button onclick="deleteData(`'. route('invoice.destroy', $invoice->id) .'`)" 
                        class="btn btn-danger btn-xs">
                    <i class="fa fa-trash"></i>           
            ';

            

            $data[] = $row;
        }

        return response()->json(['data' => $data]);
    }

    public function ProductData()
    {
        $products = Produk::with('stock')->orderBy('product_name')->get();
        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'product_id' => $product->product_id,
                'product_code' => $product->product_code,
                'product_name' => $product->product_name,
                'brand' => $product->brand ?? 'N/A',
                'variant' => $product->variant ?? 'N/A',
                'unit' => $product->unit ?? 'N/A',
                'selling_price' => $product->selling_price,
                'stock' => $product->stock ? [
                    'id' => $product->stock->id,
                    'product_id' => $product->stock->product_id,
                    'stock' => $product->stock->stock,
                    'minimum_stock' => $product->stock->minimum_stock,
                ] : null,
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

        $fromDashboard = $request->input('from_dashboard');
        return DB::transaction(function () use ($request, $subTotal, $taxAmount, $discountRate, $grandTotal, $fromDashboard) {

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

                    // If same product repeated but different price -> error
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

                    $requiredQty = (int) $m['qty'];
                    $availableQty = (int) $stock->stock;

                    if ($availableQty < $requiredQty) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'stock' => "Insufficient stock for product_id {$pid}. Available: {$availableQty}, Required: {$requiredQty}",
                        ]);
                    }
                }

                /**
                 * ✅ Atomic stock decrement with proper quantity handling
                 */
                $userId = auth()->id();
                foreach ($merged as $pid => $m) {
                    $qty = (int) $m['qty'];

                    $updated = \App\Models\ProductStock::where('product_id', $pid)
                        ->where('stock', '>=', $qty)
                        ->update([
                            'stock'      => DB::raw("stock - {$qty}"),
                            'updated_by' => $userId,
                            'updated_at' => now(),
                        ]);

                    if ($updated === 0) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'stock' => "Failed to deduct stock for product_id {$pid}. Stock may have changed. Please retry.",
                        ]);
                    }
                }

                /**
                 * ✅ Insert ONE invoice item per product_id with correct quantity
                 */
                foreach ($merged as $pid => $m) {
                    $produk = $m['product'];
                    $qty    = (int) $m['qty'];
                    $price  = (float) $m['unit_price'];
                    $totalPrice = $qty * $price;

                    InvoiceItem::create([
                        'invoice_id'     => $invoice->id,
                        'item_id'        => $produk->product_id,
                        'item_name'      => $produk->product_name,
                        'per_item_price' => $price,
                        'quantity'       => $qty,
                        'total_price'    => $totalPrice,
                    ]);
                }

            } else {
                // Non-product invoice item
                $itemName     = ucfirst($invoiceReference) . " Invoice";
                $quantity     = 1;
                $perItemPrice = $subTotal;

                InvoiceItem::create([
                    'invoice_id'     => $invoice->id,
                    'item_id'       => null,
                    'item_name'      => $itemName,
                    'per_item_price' => $perItemPrice,
                    'quantity'       => $quantity,
                    'total_price'    => $subTotal,
                ]);
            }

            // If created from dashboard, redirect to /invoice
            if ($fromDashboard) {
                return redirect('/invoice');
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

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $invoice = Invoice::with('items')->findOrFail($id);

            foreach ($invoice->items as $item) {

                // If item is product
                if (!is_null($item->item_id)) {

                    // Update product_stocks table
                    DB::table('product_stocks')
                        ->where('product_id', $item->item_id)
                        ->increment('stock', $item->quantity);
                }
            }

            // Delete invoice items
            $invoice->items()->delete();

            // Delete invoice
            $invoice->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Invoice deleted and stock restored successfully.'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
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
