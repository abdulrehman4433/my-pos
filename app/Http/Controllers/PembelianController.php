<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
                return '$ ' . format_uang($pembelian->total_price);
            })

            ->addColumn('payment', function ($pembelian) {
                return '$ ' . format_uang($pembelian->payment);
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
                    <div class="btn-group">
                        <button onclick="showDetail(`'. route('pembelian.show', $pembelian->purchase_id) .'`)"
                            class="btn btn-xs btn-primary btn-flat">
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
        $pembelian = Pembelian::findOrFail($request->id_pembelian);
        $pembelian->total_item = $request->total_item;
        $pembelian->total_harga = $request->total;
        $pembelian->diskon = $request->diskon;
        $pembelian->bayar = $request->bayar;
        $pembelian->update();

        $detail = PembelianDetail::where('id_pembelian', $pembelian->id_pembelian)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            $produk->stok += $item->jumlah;
            $produk->update();
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
                    . e($detail->product->product_code) .
                    '</span>';
            })

            ->addColumn('product_name', function ($detail) {
                return e($detail->product->product_name);
            })

            ->addColumn('purchase_price', function ($detail) {
                return '$ ' . format_uang($detail->purchase_price);
            })

            ->addColumn('quantity', function ($detail) {
                return format_uang($detail->quantity);
            })

            ->addColumn('subtotal', function ($detail) {
                return '$ ' . format_uang($detail->subtotal);
            })

            ->rawColumns(['product_code'])
            ->make(true);
    }



    public function destroy($id)
{
    // Find the purchase
    $pembelian = Pembelian::findOrFail($id);

    // Get all purchase details related to this purchase
    $details = PembelianDetail::where('purchase_id', $pembelian->purchase_id)->get();

    foreach ($details as $item) {
        // Find the related product
        $produk = Produk::find($item->product_id);

        if ($produk) {
            // Reduce stock
            $produk->stock -= $item->quantity;

            // Prevent negative stock
            if ($produk->stock < 0) {
                $produk->stock = 0;
            }

            $produk->save();
        }

        // Delete the purchase detail
        $item->delete();
    }

    // Delete the purchase
    $pembelian->delete();

    return response()->json(['message' => 'Purchase deleted successfully.'], 200);
}


}
