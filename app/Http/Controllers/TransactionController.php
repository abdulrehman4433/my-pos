<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Produk;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\TransactionReturn;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::get();

        return view('transaction.index', compact('invoices'));
    }

    public function data()
    {
        $returns = TransactionReturn::with([
                'invoice:id,invoice_reference,invoice_code,grand_total,payment_status'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [];
        $no   = 1;

        foreach ($returns as $return) {

            $invoice = $return->invoice;

            // return type label
            if ($return->return_type === 'full') {
                $returnType = '<span class="label label-danger">Full</span>';
            } else {
                $returnType = '<span class="label label-warning">Partial</span>';
            }

            // payment status from invoice
            if ($invoice && $invoice->payment_status === 'paid') {
                $paymentStatus = '<span class="label label-success">Paid</span>';
            } elseif ($invoice && $invoice->payment_status === 'partial') {
                $paymentStatus = '<span class="label label-warning">Partial</span>';
            } else {
                $paymentStatus = '<span class="label label-danger">Unpaid</span>';
            }

            $row = [];
            $row['no']                 = $no++;
            $row['return_no']          = $return->return_no;
            $row['invoice_reference']  = $invoice->invoice_reference ?? 'N/A';
            $row['invoice_code']       = $invoice->invoice_code ?? 'N/A';
            $row['return_type']        = $returnType;
            $row['return_amount']      = number_format($return->return_amount, 2);
            $row['reason']             = $return->reason ?? '-';
            $row['payment_status']     = $paymentStatus;
            $row['created_at']         = $return->created_at
                                            ? $return->created_at->format('d-m-Y')
                                            : '-';

            $data[] = $row;
        }

        return response()->json(['data' => $data]);
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
        // Sanitize numeric inputs from strings with commas
        if ($request->has('return_amount')) {
            // Remove commas and convert to float
            $request->merge([
                'return_amount' => str_replace(',', '', $request->return_amount)
            ]);
        }

        $request->validate([
            'invoice_id'       => 'required|exists:invoices,id',
            'return_type'      => 'required|in:full,partial',
            'return_amount'    => 'nullable|numeric|min:0.01',
            'return_amount_in' => 'nullable|string|max:50',
            'reason'           => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {

            $invoice = Invoice::lockForUpdate()->findOrFail($request->invoice_id);

            $hasFullReturn = TransactionReturn::where('invoice_id', $invoice->id)
                ->where('return_type', 'full')
                ->exists();

            if ($hasFullReturn) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Full return already exists for this invoice.'
                ]);
            }

            $alreadyReturned = TransactionReturn::where('invoice_id', $invoice->id)
                ->sum('return_amount');

            $invoiceTotal = $invoice->grand_total;
            $remaining    = $invoiceTotal - $alreadyReturned;

            if ($remaining <= 0) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'This invoice is already fully returned.'
                ]);
            }

            // Determine return amount
            if ($request->return_type === 'full') {
                $returnAmount = $remaining;
            } else {
                if ($request->return_amount > $remaining) {
                    throw ValidationException::withMessages([
                        'return_amount' => 'Return amount cannot exceed remaining invoice amount.'
                    ]);
                }
                $returnAmount = $request->return_amount;
            }

            // Create return transaction
            TransactionReturn::create([
                'invoice_id'       => $invoice->id,
                'return_no'        => 'RET-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'return_type'      => $request->return_type,
                'return_amount'    => $returnAmount,
                'return_amount_in' => $request->return_amount_in,
                'reason'           => $request->reason,
                'created_by'       => auth()->id(),
            ]);

            // Update invoice return status
            $newReturnedTotal = $alreadyReturned + $returnAmount;

            if ($newReturnedTotal >= $invoiceTotal) {
                $invoice->return_status = 'full';
            } elseif ($newReturnedTotal > 0) {
                $invoice->return_status = 'partial';
            }

            $invoice->save();
        });

        return response()->json([
            'message' => 'Transaction return processed successfully'
        ]);
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
        //
    }

    public function partialTransactionData()
    {
        $invoices = Invoice::query()
            ->where('payment_status', 'partial')
            ->where('return_status', '!=', 'full')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [];
        $no   = 1;

        foreach ($invoices as $invoice) {

            // Payment Status Badge
            if ($invoice->payment_status === 'paid') {
                $paymentStatus = '<span class="label label-success">Paid</span>';
            } elseif ($invoice->payment_status === 'partial') {
                $paymentStatus = '<span class="label label-warning">Partial</span>';
            } else {
                $paymentStatus = '<span class="label label-danger">Unpaid</span>';
            }

            // Return Status Badge
            if ($invoice->return_status === 'partial') {
                $returnStatus = '<span class="label label-warning">Partial Return</span>';
            } elseif ($invoice->return_status === 'full') {
                $returnStatus = '<span class="label label-danger">Full Return</span>';
            } else {
                $returnStatus = '<span class="label label-default">None</span>';
            }

            $data[] = [
                'no'                => $no++,
                'id'                => $invoice->id,
                'invoice_reference' => $invoice->invoice_reference,
                'invoice_code'      => $invoice->invoice_code,
                'sub_total'         => number_format($invoice->sub_total, 2),
                'discount_amount'   => number_format($invoice->discount_amount, 2),
                'grand_total'       => number_format($invoice->grand_total, 2),
                'returned_amount'   => number_format($invoice->returned_amount, 2),
                'received_amount'   => number_format($invoice->received_amount, 2),
                'remaining_amount'  => number_format($invoice->remaining_amount, 2),
                'payment_status'    => $paymentStatus,
                'return_status'     => $returnStatus,
                'created_at'        => $invoice->created_at
                                        ? $invoice->created_at->format('d-m-Y')
                                        : '-',
            ];
        }

        return view('partial-transaction.index', [
            'invoices' => $data
        ]);
    }

    public function invoicePartialTransaction($id)
    {
        $invoice = Invoice::findOrFail($id);

        return response()->json([
            'id'                => $invoice->id,
            'invoice_reference' => $invoice->invoice_reference,
            'invoice_code'      => $invoice->invoice_code,
            'sub_total'         => number_format($invoice->sub_total, 2),
            'discount_amount'   => number_format($invoice->discount_amount, 2),
            'grand_total'       => number_format($invoice->grand_total, 2),
            'returned_amount'   => number_format($invoice->returned_amount, 2),
            'received_amount'   => number_format($invoice->received_amount, 2),
            'remaining_amount'  => number_format($invoice->remaining_amount, 2),
            'payment_status'    => $invoice->payment_status,
            'return_status'     => $invoice->return_status,
        ]);
    }

    public function clearInvoiceRemainingAmount(Request $request)
    {
        $request->validate([
            'invoice_id'       => 'required|exists:invoices,id',
            'received_amount'  => 'required',
        ]);

        DB::transaction(function () use ($request) {

            $invoice = Invoice::lockForUpdate()->findOrFail($request->invoice_id);

            $receivedAmount   = (float) str_replace(',', '', $request->received_amount);
            $currentReceived  = (float) $invoice->received_amount;
            $currentRemaining = (float) $invoice->remaining_amount;

            $invoice->received_amount  = $currentReceived + $receivedAmount;
            $invoice->remaining_amount = $currentRemaining - $receivedAmount;

            if ($invoice->remaining_amount <= 0) {
                $invoice->payment_status = 'paid';
                $invoice->remaining_amount = 0;
            } else {
                $invoice->payment_status = 'partial';
            }

            $invoice->save();
        });

        return redirect()
        ->back()
        ->with('success', 'Invoice remaining amount cleared successfully');
    }

}