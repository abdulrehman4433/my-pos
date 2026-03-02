<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('penjualan.index');
    }

    public function data(Request $request)
    {
        $invoices = Invoice::with(['creator', 'items.product'])
            ->where('payment_status', 'paid')
            ->whereNotIn('return_status', ['full'])
            ->orderBy('created_at', 'desc')
            ->get();

        return datatables()
            ->of($invoices)
            ->addIndexColumn()

            ->addColumn('invoice_date', function ($invoice) {
                return tanggal_indonesia($invoice->created_at, false);
            })

            ->addColumn('customer_code', function ($invoice) {
                return '<span class="label label-success">'. $invoice->invoice_code .'</span>';
            })

            // ✅ FIXED: use loaded collection instead of query
            ->addColumn('quantity', function ($invoice) {
                return $invoice->items->sum('quantity');
            })

            ->addColumn('total_amount', function ($invoice) {
                return $invoice->sub_total;
            })

            ->addColumn('discount_amount', function ($invoice) {
                return $invoice->discount_amount.' %';
            })

            ->addColumn('final_amount', function ($invoice) {
                return $invoice->grand_total;
            })

            // ✅ NEW PROFIT COLUMN
            ->addColumn('profit', function ($invoice) {

                $totalProfit = 0;

                foreach ($invoice->items as $item) {

                    if (!is_null($item->item_id) && $item->product) {

                        $purchasePrice = $item->product->purchase_price;
                        $sellingPrice  = $item->product->selling_price;

                        $totalProfit += ($sellingPrice - $purchasePrice) * $item->quantity;
                    }
                }

                // subtract invoice discount
                if ($invoice->discount_amount > 0) {
                    $discountValue = ($totalProfit * $invoice->discount_amount) / 100;
                    $totalProfit -= $discountValue;
                }

                return $totalProfit;
            })

            ->addColumn('cashier_name', function ($invoice) {
                return $invoice->creator->name ?? '-';
            })

            ->addColumn('aksi', function ($invoice) {
                return '
                    <button onclick="showDetail(`'. route('penjualan.show', $invoice->id) .'`)" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData(`'. route('penjualan.destroy', $invoice->id) .'`)" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                ';
            })

            ->rawColumns(['aksi', 'customer_code'])
            ->make(true);
    }

    public function create()
    {
        $penjualan = new Penjualan();
        $penjualan->id_member = null;
        $penjualan->total_item = 0;
        $penjualan->total_harga = 0;
        $penjualan->diskon = 0;
        $penjualan->bayar = 0;
        $penjualan->diterima = 0;
        $penjualan->id_user = auth()->id();
        $penjualan->save();

        session(['id_penjualan' => $penjualan->id_penjualan]);
        return redirect()->route('transaksi.index');
    }

    public function store(Request $request)
    {
        $penjualan = Penjualan::findOrFail($request->id_penjualan);
        $penjualan->id_member = $request->id_member;
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $request->total;
        $penjualan->diskon = $request->diskon;
        $penjualan->bayar = $request->bayar;
        $penjualan->diterima = $request->diterima;
        $penjualan->update();

        $detail = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $item->diskon = $request->diskon;
            $item->update();

            $produk = Produk::find($item->id_produk);
            $produk->stok -= $item->jumlah;
            $produk->update();
        }

        return redirect()->route('transaksi.selesai');
    }

    public function show($id)
{
    $invoice = Invoice::with('items')->findOrFail($id);
    $detail  = $invoice->items;

    return datatables()
        ->of($detail)
        ->addIndexColumn()

        ->addColumn('kode_produk', function ($item) {
            return '<span class="label label-success">'. $item->product_code .'</span>';
        })

        ->addColumn('nama_produk', function ($item) {
            return $item->product_name;
        })

        ->addColumn('jumlah', function ($item) {
            return $item->quantity;
        })

        // ✅ Invoice Level Columns (Plain Values)
        ->addColumn('sub_total', function () use ($invoice) {
            return (float) $invoice->sub_total;
        })

        ->addColumn('discount', function () use ($invoice) {
            return (float) $invoice->discount_amount.' %'; // percentage
        })

        ->addColumn('grand_total', function () use ($invoice) {
            return (float) $invoice->grand_total;
        })

        ->rawColumns(['kode_produk'])
        ->make(true);
}

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

    public function selesai()
    {
        $setting = Setting::first();

        return view('penjualan.selesai', compact('setting'));
    }

    public function notaKecil()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();
        
        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
    }

    public function notaBesar()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        $pdf = PDF::loadView('penjualan.nota_besar', compact('setting', 'penjualan', 'detail'));
        $pdf->setPaper(0,0,609,440, 'potrait');
        return $pdf->stream('Transaction-'. date('Y-m-d-his') .'.pdf');
    }
}