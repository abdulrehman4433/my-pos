<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Produk;
use App\Models\ProductStock;
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
            $row['return_amount_in']   = $return->return_amount_in ?? '-';
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

    public function view($id)
    {
        //
    }

    public function partialTransactionData()
    {
        $invoices = Invoice::query()
            ->whereIn('payment_status', ['partial', 'unpaid'])
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
                'remaining_amount'  => number_format($invoice->grand_total - $invoice->received_amount, 2),
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
        $invoice = Invoice::with(['items.product'])
            ->whereHas('items')
            ->findOrFail($id);

        return response()->json([
            'id'                => $invoice->id,
            'invoice_reference' => $invoice->invoice_reference,
            'invoice_code'      => $invoice->invoice_code,
            'sub_total'         => number_format($invoice->sub_total, 2),
            'discount_amount'   => number_format($invoice->discount_amount, 2),
            'grand_total'       => number_format($invoice->grand_total, 2),
            'returned_amount'   => number_format($invoice->returned_amount, 2),
            'received_amount'   => number_format($invoice->received_amount, 2),
            'remaining_amount'  => number_format($invoice->grand_total - $invoice->received_amount, 2),
            'payment_status'    => $invoice->payment_status,
            'return_status'     => $invoice->return_status,

            'items' => $invoice->items->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'item_id'       => $item->item_id,
                    'item_name'     => $item->item_name,
                    'product_name'  => $item->product->item_name ?? null,
                    'price'         => number_format($item->per_item_price, 2),
                    'quantity'      => $item->quantity,
                    'total_price'   => number_format($item->total_price, 2),
                    'return_status' => $item->return_status,
                ];
            }),
        ]);
    }


    public function clearInvoiceRemainingAmount(Request $request)
    {
        $request->validate([
            'invoice_id'       => 'required|exists:invoices,id',
            'received_amount'  => 'required',
            'remaining_amount' => 'required',
        ]);

        DB::transaction(function () use ($request) {

            $invoice = Invoice::lockForUpdate()->findOrFail($request->invoice_id);

            $currentReceived  = (float) $invoice->received_amount;
            $currentRemaining = (float) $invoice->remaining_amount;

            $invoice->received_amount  = $currentReceived + $currentRemaining;
            $invoice->remaining_amount = 0;

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

    public function store(Request $request)
{
    $request->validate([
        'invoice_id'        => 'required|exists:invoices,id',
        'return_type'       => 'required|in:full,partial',
        'items.*.return_qty'=> 'nullable|numeric|min:0',
        'reason'            => 'nullable|string|max:500',
        'return_amount_in'  => 'nullable|string',
    ]);

    $invoice = Invoice::with('items')->findOrFail($request->invoice_id);
    $totalReturned = 0;

    DB::transaction(function () use ($invoice, $request, &$totalReturned) {

        // Calculate discount ratio (grand_total / sub_total) for proper adjustment
        $discountRatio = $invoice->sub_total > 0 ? ($invoice->grand_total / $invoice->sub_total) : 1;

        if ($request->return_type === 'full') {
            // FULL RETURN: Zero out all relevant columns
            foreach ($invoice->items as $item) {
                $returnQty = $item->quantity;
                if ($returnQty <= 0) continue;

                $itemPrice    = $item->per_item_price ?? ($item->total_price / max($item->quantity, 1));
                $rawReturn    = $returnQty * $itemPrice;
                $returnAmount = $rawReturn * $discountRatio;

                // Update invoice item
                $item->quantity = 0;
                $item->total_price = 0;
                $item->per_item_price = 0;
                $item->returned_amount += $returnAmount;
                $item->return_status = 'full';
                $item->save();

                // Update stock
                if (!is_null($item->item_id)) {
                    $stock = ProductStock::firstOrCreate(
                        ['product_id' => $item->item_id],
                        ['stock' => 0, 'created_by' => auth()->id()]
                    );
                    $stock->increment('stock', $returnQty);
                }

                $totalReturned += $returnAmount;
            }

            // Update invoice totals
            $invoice->sub_total = 0;
            $invoice->tax_amount = 0;
            $invoice->discount_amount = 0;
            $invoice->grand_total = 0;
            $invoice->received_amount = 0;
            $invoice->remaining_amount = 0;
            $invoice->returned_amount += $totalReturned;
            $invoice->return_status = 'full';

        } elseif ($request->return_type === 'partial') {
            // PARTIAL RETURN: Calculate exact values
            $itemsData = $request->input('items', []);

            foreach ($invoice->items as $item) {
                $returnQty = isset($itemsData[$item->id]['return_qty']) 
                    ? (float)$itemsData[$item->id]['return_qty'] 
                    : 0;

                if ($returnQty <= 0 || $returnQty > $item->quantity) continue;

                $itemPrice    = $item->per_item_price ?? ($item->total_price / max($item->quantity, 1));
                $rawReturn    = $returnQty * $itemPrice;
                $returnAmount = $rawReturn * $discountRatio;

                // Update invoice item
                $item->quantity -= $returnQty;
                $item->total_price -= $rawReturn;
                $item->returned_amount += $returnAmount;
                $item->return_status = $item->quantity > 0 ? 'partial' : 'full';
                $item->save();

                // Update stock
                if (!is_null($item->item_id)) {
                    $stock = ProductStock::firstOrCreate(
                        ['product_id' => $item->item_id],
                        ['stock' => 0, 'created_by' => auth()->id()]
                    );
                    $stock->increment('stock', $returnQty);
                }

                $totalReturned += $returnAmount;
            }

            // Check remaining invoice amount
            $remaining = $invoice->grand_total - $invoice->returned_amount;
            if ($totalReturned > $remaining) {
                throw ValidationException::withMessages([
                    'items' => ['Calculated return exceeds remaining invoice amount.'],
                ]);
            }

            $invoice->returned_amount += $totalReturned;

            // Update invoice totals proportionally
            $rawTotalReturned = $totalReturned / $discountRatio;
            $invoice->sub_total -= $rawTotalReturned;
            $invoice->grand_total -= $totalReturned;

            // Recalculate remaining amount
            $invoice->remaining_amount = max($invoice->grand_total - $invoice->received_amount, 0);

            $allReturned = $invoice->items->every(fn($i) => $i->quantity == 0);
            $invoice->return_status = $allReturned ? 'full' : 'partial';
        }

        $invoice->save();

        // Record return transaction
        TransactionReturn::create([
            'invoice_id'       => $invoice->id,
            'return_no'        => 'RTN-' . strtoupper(Str::random(8)),
            'return_type'      => $request->return_type,
            'return_amount'    => $totalReturned,
            'return_amount_in' => $request->return_amount_in,
            'reason'           => $request->reason,
            'created_by'       => auth()->id(),
        ]);
    });

    return response()->json([
        'success' => true,
        'message' => 'Return processed successfully',
        'total_returned' => number_format($totalReturned, 2),
    ]);
}
    
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'invoice_id'        => 'required|exists:invoices,id',
    //         'return_type'       => 'required|in:full,partial',
    //         'items.*.return_qty'=> 'nullable|numeric|min:0',
    //         'reason'            => 'nullable|string|max:500',
    //         'return_amount_in'  => 'nullable|string',
    //     ]);

    //     $invoice = Invoice::with('items')->findOrFail($request->invoice_id);

    //     $totalReturned = 0;

    //     DB::transaction(function () use ($invoice, $request, &$totalReturned) {

    //         // Calculate discount ratio once (applies to all items)
    //         $discountRatio = $invoice->grand_total > 0
    //             ? ($invoice->grand_total / $invoice->sub_total)
    //             : 1; 
    //         // Example: if sub_total=1000, grand_total=900 → discountRatio=0.9

    //         if ($request->return_type === 'full') {
    //             foreach ($invoice->items as $item) {
    //                 $returnQty = $item->quantity;
    //                 if ($returnQty <= 0) continue;

    //                 $itemPrice    = $item->per_item_price ?? ($item->total_price / $item->quantity);
    //                 $rawReturn    = $returnQty * $itemPrice;
    //                 $returnAmount = $rawReturn * $discountRatio; // ✅ apply discount

    //                 // Update invoice item
    //                 $item->returned_amount += $returnAmount;
    //                 $item->quantity = 0;
    //                 $item->return_status = 'full';
    //                 $item->total_price -= $rawReturn; // keep raw total_price consistent
    //                 $item->save();

    //                 // Update product stock
    //                 if (!is_null($item->item_id)) {
    //                     $stock = ProductStock::firstOrCreate(
    //                         ['product_id' => $item->item_id],
    //                         ['stock' => 0, 'created_by' => auth()->id()]
    //                     );
    //                     $stock->increment('stock', $returnQty);
    //                 }

    //                 $totalReturned += $returnAmount;
    //             }

    //             $invoice->returned_amount += $totalReturned;
    //             $invoice->return_status = 'full';

    //         } elseif ($request->return_type === 'partial') {
    //             $itemsData = $request->input('items', []);

    //             foreach ($invoice->items as $item) {
    //                 $returnQty = isset($itemsData[$item->id]['return_qty'])
    //                     ? (float)$itemsData[$item->id]['return_qty']
    //                     : 0;

    //                 if ($returnQty <= 0 || $returnQty > $item->quantity) continue;

    //                 $itemPrice    = $item->per_item_price ?? ($item->total_price / $item->quantity);
    //                 $rawReturn    = $returnQty * $itemPrice;
    //                 $returnAmount = $rawReturn * $discountRatio; // ✅ apply discount

    //                 // Update invoice item
    //                 $item->returned_amount += $returnAmount;
    //                 $item->quantity -= $returnQty;
    //                 $item->return_status = $item->quantity > 0 ? 'partial' : 'full';
    //                 $item->total_price -= $rawReturn;
    //                 $item->save();

    //                 // Update product stock
    //                 if (!is_null($item->item_id)) {
    //                     $stock = ProductStock::firstOrCreate(
    //                         ['product_id' => $item->item_id],
    //                         ['stock' => 0, 'created_by' => auth()->id()]
    //                     );
    //                     $stock->increment('stock', $returnQty);
    //                 }

    //                 $totalReturned += $returnAmount;
    //             }

    //             // Validate against remaining refundable balance
    //             $remaining = $invoice->grand_total - $invoice->returned_amount;
    //             if ($totalReturned > $remaining) {
    //                 throw ValidationException::withMessages([
    //                     'items' => ['Calculated return exceeds remaining invoice amount.'],
    //                 ]);
    //             }

    //             $invoice->returned_amount += $totalReturned;
    //             $allReturned = $invoice->items->every(fn($i) => $i->quantity == 0);
    //             $invoice->return_status = $allReturned ? 'full' : 'partial';
    //         }

    //         // ✅ Subtract discounted return from grand_total
    //         $invoice->grand_total -= $totalReturned;

    //         // ✅ Sub_total should subtract raw return (before discount)
    //         $rawTotalReturned = $totalReturned / $discountRatio;
    //         $invoice->sub_total -= $rawTotalReturned;

    //         $invoice->save();

    //         // ✅ Insert into transaction_returns with discounted amount
    //         TransactionReturn::create([
    //             'invoice_id'       => $invoice->id,
    //             'return_no'        => 'RTN-' . strtoupper(Str::random(8)),
    //             'return_type'      => $request->return_type,
    //             'return_amount'    => $totalReturned, // discounted amount
    //             'return_amount_in' => $request->return_amount_in,
    //             'reason'           => $request->reason,
    //             'created_by'       => auth()->id(),
    //         ]);
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Return processed successfully',
    //         'total_returned' => number_format($totalReturned, 2),
    //     ]);
    // }

}