<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Produk;
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

                

             
            ';

            //    <button onclick="editForm(`'.route('invoice.edit', $invoice->id).'`)" 
            //             class="btn btn-warning btn-xs">
            //         <i class="fa fa-edit"></i>
            //     </button>

            //     <button onclick="deleteData(`'.route('invoice.destroy', $invoice->id).'`)" 
            //             class="btn btn-danger btn-xs">
            //         <i class="fa fa-trash"></i>
            //     </button>

            // <button onclick="editForm(`'.route('invoice.edit', $invoice->id).'`)" 
            //             class="btn btn-warning btn-xs">
            //         <i class="fa fa-file-pdf-o"></i>
            //     </button>

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
    //         'invoice_reference'  => ['required', 'string', 'max:255'], // e.g., 'product'
    //         'sub_total'          => ['required', 'numeric', 'min:0'],
    //         'tax_amount'         => ['nullable', 'numeric', 'min:0'],
    //         'discount_amount'    => ['nullable', 'numeric', 'min:0'],
    //         'payment_method'     => ['required', 'string', 'max:50'],
    //         'payment_status'     => ['required', 'string', Rule::in(['paid','unpaid','partial'])],
    //         // If reference is product, enforce the structure of products array
    //         'products'           => ['nullable', 'array'],
    //         'products.*.id'      => ['required_if:invoice_reference,product', 'integer'],
    //         'products.*.qty'     => ['required_if:invoice_reference,product', 'integer', 'min:1'],
    //         'products.*.price'   => ['nullable', 'numeric', 'min:0'],
    //     ]);

    //     // Compute totals
    //     $subTotal       = (float) $request->input('sub_total', 0);
    //     $taxAmount      = (float) $request->input('tax_amount', 0);
    //     $discountAmount = (float) $request->input('discount_amount', 0);

    //     $grandTotal = $subTotal + $taxAmount - max(0, $discountAmount);

    //     return DB::transaction(function () use ($request, $subTotal, $taxAmount, $discountAmount, $grandTotal) {

    //         // Create invoice
    //         $invoice = Invoice::create([
    //             'invoice_code'      => $request->input('invoice_code'),
    //             'invoice_reference' => $request->input('invoice_reference'),
    //             'sub_total'         => $subTotal,
    //             'tax_amount'        => $taxAmount,
    //             'discount_amount'   => $discountAmount,
    //             'grand_total'       => $grandTotal,
    //             'payment_received'  => $request->input('payment_method'), // confirm column name vs meaning
    //             'payment_status'    => $request->input('payment_status'),
    //             'created_by'        => auth()->id(),
    //             'updated_by'        => auth()->id(),
    //         ]);

    //         // If it's a product invoice, add items
    //         if ($request->input('invoice_reference') === 'product') {
    //             $products = $request->input('products', []);

    //             // Skip if empty or not an array
    //             if (is_array($products) && count($products) > 0) {
    //                 foreach ($products as $single_product) {
    //                     // $single_product is an array: ['id' => ..., 'qty' => ..., 'price' => ...]
    //                     $productId = $single_product['id'] ?? null;
    //                     $qty       = (int) ($single_product['qty'] ?? 0);

    //                     if (!$productId || $qty < 1) {
    //                         // You could throw a ValidationException or just continue
    //                         continue;
    //                     }

    //                     // Fetch product (your model name looks like "Produk"; confirm)
    //                     $product = Produk::find($productId);
    //                     if (!$product) {
    //                         // Optional: throw an exception to abort the transaction
    //                         // throw new \RuntimeException("Product {$productId} not found.");
    //                         continue;
    //                     }

    //                     // Prefer the product's selling_price as the source of truth
    //                     $unitPrice  = (float) ($product->selling_price);
    //                     $lineTotal  = $unitPrice * $qty;

    //                     InvoiceItem::create([
    //                         'invoice_id'      => $invoice->id,
    //                         'item_name'       => $product->product_name,
    //                         'per_item_price'  => $unitPrice,
    //                         'quantity'        => $qty,
    //                         'total_price'     => $lineTotal,
    //                     ]);
    //                 }
    //             }
    //         }

    //         return response()->json([
    //             'success'     => true,
    //             'message'     => 'Invoice created successfully!',
    //             'invoice_id'  => $invoice->id,
    //             'grand_total' => $grandTotal,
    //         ]);
    //     }, 3); // retry up to 3 times on deadlocks
    // }
   public function store(Request $request)
{
    // Base validation
    $request->validate([
        'invoice_code'       => ['required', 'string', 'max:255'],
        'invoice_reference'  => ['required', 'string', 'max:255'],
        'sub_total'          => ['required', 'numeric', 'min:0'],
        'tax_amount'         => ['nullable', 'numeric', 'min:0'],
        'discount_amount'    => ['nullable', 'numeric', 'min:0'],
        'payment_method'     => ['required', 'string', 'max:50'],
        'payment_status'     => ['required', 'string', Rule::in(['paid','unpaid','partial'])],
        // Products array - required only for product invoices
        'products'           => ['nullable', 'array'],
        'products.*.id'      => ['required_if:invoice_reference,product', 'integer'],
        'products.*.qty'     => ['required_if:invoice_reference,product', 'integer', 'min:1'],
        'products.*.price'   => ['nullable', 'numeric', 'min:0'],
        // For non-product invoices
        'reference_id'       => ['nullable', 'integer'], // Changed to integer
    ]);

    // Compute totals
    $subTotal       = (float) $request->input('sub_total', 0);
    $taxAmount      = (float) $request->input('tax_amount', 0);
    $discountAmount = (float) $request->input('discount_amount', 0);

    $grandTotal = $subTotal + $taxAmount - max(0, $discountAmount);

    return DB::transaction(function () use ($request, $subTotal, $taxAmount, $discountAmount, $grandTotal) {

        // Determine invoice resource and resource ID based on reference
        $invoiceReference = $request->input('invoice_reference');
        $referenceId = $request->input('reference_id');
        
        // Handle empty reference_id - set to null if empty string or not provided
        if ($referenceId === '' || $referenceId === null) {
            $referenceId = null;
        } else {
            // Convert to integer if it's a valid number
            $referenceId = (int) $referenceId;
        }
        
        // Map reference to resource type (you can adjust this mapping as needed)
        $resourceMapping = [
            'product' => 'product',
            'project' => 'project', 
            'maintenance' => 'maintenance',
            // Add other mappings as needed
        ];
        
        $invoiceResource = $resourceMapping[$invoiceReference] ?? $invoiceReference;

        // Create invoice
        $invoice = Invoice::create([
            'invoice_code'        => $request->input('invoice_code'),
            'invoice_reference'   => $invoiceReference,
            'invoice_resource'    => $invoiceResource,
            'invoice_resource_id' => $referenceId, // Now properly null or integer
            'sub_total'           => $subTotal,
            'tax_amount'          => $taxAmount,
            'discount_amount'     => $discountAmount,
            'grand_total'         => $grandTotal,
            'payment_received'    => $request->input('payment_method'),
            'payment_status'      => $request->input('payment_status'),
            'created_by'          => auth()->id(),
            'updated_by'          => auth()->id(),
        ]);

        // IMPORTANT: For non-product invoices, ignore products array if provided
        if ($invoiceReference === 'product') {
            $products = $request->input('products', []);

            if (is_array($products) && count($products) > 0) {
                foreach ($products as $single_product) {
                    $productId = $single_product['id'] ?? null;
                    $qty       = (int) ($single_product['qty'] ?? 0);
                    $price     = (float) ($single_product['price'] ?? 0);

                    if (!$productId || $qty < 1) {
                        continue;
                    }

                    $product = Produk::find($productId);
                    if (!$product) {
                        continue;
                    }

                    // Use provided price or fall back to product's selling_price
                    $unitPrice = $price > 0 ? $price : (float) ($product->selling_price);
                    $lineTotal = $unitPrice * $qty;

                    InvoiceItem::create([
                        'invoice_id'      => $invoice->id,
                        'item_name'       => $product->product_name,
                        'per_item_price'  => $unitPrice,
                        'quantity'        => $qty,
                        'total_price'     => $lineTotal,
                    ]);
                }
            }
        } else {
            // For non-product invoices (like project, maintenance, etc.)
            // Create a single invoice item with the reference as item name
            $itemName = ucfirst($invoiceReference) . " Invoice";
            $quantity = 1; // Default quantity for non-product invoices
            $perItemPrice = $subTotal; // Use sub_total as the price
            
            InvoiceItem::create([
                'invoice_id'      => $invoice->id,
                'item_name'       => $itemName,
                'per_item_price'  => $perItemPrice,
                'quantity'        => $quantity,
                'total_price'     => $subTotal, // total_price equals sub_total for single item
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

    public function exportInvoicePDF($id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('invoice.pdf', compact('invoice'));

        return $pdf->download('invoice_' . $invoice->invoice_code . '.pdf');
    }
}
