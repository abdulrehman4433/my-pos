<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    public function index()
    {
        $supplier = Supplier::orderBy('name')->get();

        return view('pembelian.index', compact('supplier'));
    }

    public function data()
    {
        $pembelian = Pembelian::orderBy('purchase_id', 'desc')->get();

        return datatables()
            ->of($pembelian)
            ->addIndexColumn()

            ->addColumn('total_items', function ($pembelian) {
                return format_uang($pembelian->total_items);
            })

            ->addColumn('total_price', function ($pembelian) {
                return 'RS ' . format_uang($pembelian->total_price);
            })

            ->addColumn('payment', function ($pembelian) {
                return 'RS ' . format_uang($pembelian->payment);
            })

            ->addColumn('date', function ($pembelian) {
                return tanggal_indonesia($pembelian->created_at, false);
            })

            ->addColumn('supplier', function ($pembelian) {
                return $pembelian->supplier->name;
            })

            ->editColumn('discount', function ($pembelian) {
                return $pembelian->discount . '%';
            })

            ->addColumn('action', function ($pembelian) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button onclick="showDetail(`'. route('pembelian.show', $pembelian->purchase_id) .'`)"
                            class="btn btn-xs btn-primary btn-flat" style="margin-right: 5px;">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button onclick="deleteData(`'. route('pembelian.destroy', $pembelian->purchase_id) .'`)"
                            class="btn btn-xs btn-danger btn-flat">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    public function create($supplierId)
    {
        return DB::transaction(function () use ($supplierId) {

            // Ensure supplier exists
            $supplier = Supplier::findOrFail($supplierId);

            // Prevent duplicate active purchase
            if (session()->has('purchase_id')) {
                return redirect()->route('pembelian_detail.index');
            }

            $pembelian = Pembelian::create([
                'supplier_id' => $supplier->supplier_id,
                'total_items' => 0,
                'total_price' => 0,
                'discount'    => 0,
                'payment'     => 0,
                'branch_id'   => auth()->user()->branch_id ?? null,
            ]);

            session([
                'purchase_id' => $pembelian->purchase_id,
                'supplier_id' => $supplier->supplier_id,
            ]);

            return redirect()->route('pembelian_detail.index');
        });
    }

    public function store(Request $request)
    {
        $pembelian = Pembelian::where('purchase_id', $request->id_pembelian)->firstOrFail();
        $pembelian->total_items = $request->total_item;
        $pembelian->total_price = $request->total;
        $pembelian->discount = $request->diskon;
        $pembelian->payment = $request->bayar;
        $pembelian->update();

        $detail = PembelianDetail::where('purchase_id', $pembelian->purchase_id)->get();

        foreach ($detail as $item) {

            $stock = ProductStock::where('product_id', $item->product_id)->first();

            if ($stock) {
                // Update existing stock
                $stock->increment('stock', $item->quantity);
                $stock->update([
                    'updated_by' => Auth::id(),
                ]);
            } else {
                // Create new stock record
                ProductStock::create([
                    'product_id'    => $item->product_id,
                    'stock'         => $item->quantity,
                    'minimum_stock' => 0,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                ]);
            }
        }

        return redirect()->route('pembelian.index');
    }

    public function show($purchaseId)
    {
        $details = PembelianDetail::with('product')
            ->where('purchase_id', $purchaseId)
            ->get();

        return datatables()
            ->of($details)
            ->addIndexColumn()

            ->addColumn('product_code', function ($detail) {
                return '<span class="label label-success">'
                    . e($detail->product->product_code ?? '-') .
                    '</span>';
            })

            ->addColumn('product_name', function ($detail) {
                return e($detail->product->product_name ?? '-');
            })

            ->addColumn('purchase_price', function ($detail) {
                return 'RS ' . format_uang($detail->purchase_price);
            })

            ->addColumn('quantity', function ($detail) {
                return format_uang($detail->quantity);
            })

            ->addColumn('subtotal', function ($detail) {
                return 'RS ' . format_uang($detail->subtotal);
            })

            ->rawColumns(['product_code'])
            ->make(true);
    }



    public function destroy($id)
    {
        // Find the purchase
        $pembelian = Pembelian::findOrFail($id);

        // Get all purchase details for this purchase
        $details = PembelianDetail::where('purchase_id', $pembelian->purchase_id)->get();

        foreach ($details as $item) {
            // Find the product stock record
            $productStock = ProductStock::where('product_id', $item->product_id)->first();

            if ($productStock) {
                // Subtract the purchased quantity
                $productStock->stock -= $item->quantity;

                // Prevent negative stock
                if ($productStock->stock < 0) {
                    $productStock->stock = 0;
                }

                $productStock->save();
            }

            // Delete the purchase detail
            $item->delete();
        }

        // Delete the purchase
        $pembelian->delete();

        return response()->json(['message' => 'Purchase deleted successfully.'], 200);
    }


}
