<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PembelianDetailController extends Controller
{
    public function index()
    {
        $purchaseId = session('purchase_id');
        $products   = Produk::orderBy('product_name')->get();
        $supplier   = Supplier::find(session('supplier_id'));
        $discount   = Pembelian::find($purchaseId)->discount ?? 0;

        if (! $supplier) {
            abort(404);
        }

        return view('pembelian_detail.index', compact('purchaseId', 'products', 'supplier', 'discount'));
    }

    public function data($purchaseId)
    {
        $details = PembelianDetail::with('product')
            ->where('purchase_id', $purchaseId)
            ->get();

        $total = 0;
        $totalQuantity = 0;

        return datatables()
            ->of($details)
            ->addIndexColumn()

            ->addColumn('product_code', function ($item) {
                return '<span class="label label-success">'
                    . $item->product->product_code .
                    '</span>';
            })

            ->addColumn('product_name', function ($item) {
                return $item->product->product_name;
            })

            ->addColumn('purchase_price', function ($item) {
                return '$ ' . format_uang($item->purchase_price);
            })

            ->addColumn('quantity', function ($item) {
                return '<input type="number"
                        class="form-control input-sm quantity"
                        data-id="' . $item->purchase_detail_id . '"
                        value="' . $item->quantity . '">';
            })

            ->addColumn('subtotal', function ($item) use (&$total, &$totalQuantity) {
                $subtotal = $item->purchase_price * $item->quantity;
                $total += $subtotal;
                $totalQuantity += $item->quantity;

                return '$ ' . format_uang($subtotal);
            })

            ->addColumn('action', function ($item) {
                return '
                    <div class="btn-group">
                        <button onclick="deleteData(`' .
                            route('pembelian_detail.destroy', $item->purchase_detail_id) .
                        '`)"
                        class="btn btn-xs btn-danger btn-flat">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>';
            })

            ->rawColumns(['product_code', 'quantity', 'action'])

            // ✅ Send totals cleanly
            ->with([
                'total' => $total,
                'total_quantity' => $totalQuantity,
            ])

            ->make(true);
    }


    public function store(Request $request)
    {
        $product = Produk::find($request->product_id);

        if (! $product) {
            return response()->json('Data failed to save', 400);
        }

        $detail = PembelianDetail::create([
            'purchase_id'    => $request->purchase_id,
            'product_id'     => $product->product_id,
            'purchase_price' => $product->purchase_price,
            'quantity'       => 1,
            'subtotal'       => $product->purchase_price,
        ]);

        return response()->json('Data saved successfully', 200);
    }

    public function update(Request $request, $id)
    {
        $detail = PembelianDetail::findOrFail($id);
        $detail->quantity = $request->quantity;
        $detail->subtotal = $detail->purchase_price * $request->quantity;
        $detail->save();
    }

    public function destroy($id)
    {
        $detail = PembelianDetail::findOrFail($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($discount, $total)
    {
        $payable = $total - ($discount / 100 * $total);

        return response()->json([
            'totalrp'   => format_uang($total),
            'bayar'     => $payable,
            'bayarrp'   => format_uang($payable),
            'terbilang' => ucwords(terbilang($payable) . ' Dollar')
        ]);
    }

}
