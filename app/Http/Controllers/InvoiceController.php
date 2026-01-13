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
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'invoice_reference' => 'required|string|max:255',
            'reference_id' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
            'sub_total' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string|in:paid,unpaid,partial',
        ]);

        DB::beginTransaction();

        try {
            // Calculate grand total
            $subTotal = $request->sub_total;
            $taxAmount = $request->tax_amount ?? 0;
            $discountAmount = $request->discount_amount ?? 0;

            $grandTotal = $subTotal + $taxAmount;
            if ($discountAmount > 0) {
                $grandTotal -= $discountAmount;
            }
            $referenceId = $request->filled('reference_id')
            ? (int) $request->reference_id
            : null;

            // Create Invoice
            $invoice = Invoice::create([
                'invoice_code' => Str::upper(Str::random(6)),
                'invoice_reference' => $request->invoice_reference,
                'reference_id' => $referenceId,
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'grand_total' => $grandTotal,
                'payment_received' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // If it's a product invoice, create InvoiceItem
            if (
                $request->invoice_reference === 'product'
                && $referenceId !== null
                && $request->filled('quantity')
            ) {
                $product = Produk::find($referenceId);

                if ($product) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_name' => $product->product_name,
                        'per_item_price' => $product->selling_price,
                        'quantity' => $request->quantity,
                        'total_price' => $product->selling_price * $request->quantity,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully!',
                'invoice_id' => $invoice->id,
                'grand_total' => $grandTotal,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
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
