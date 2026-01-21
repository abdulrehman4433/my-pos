<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Member;
use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $kategori = Kategori::count();
        $produk = Produk::count();
        $supplier = Supplier::count();
        $member = Member::count();
        $penjualan = Invoice::sum('grand_total');
        $total_invoice = Invoice::get();
        $pengeluaran = Pengeluaran::sum('amount');
        $pembelian = Produk::sum('purchase_price');
        $lowStockProducts = Produk::whereColumn('stock', '<=', 'minimum_stock')->get();



        $profitReport = DB::table('invoice_items as ii')
            ->join('invoices as i', 'ii.invoice_id', '=', 'i.id')
            ->join('products as p', 'ii.item_id', '=', 'p.product_id')
            ->select(
                'i.id as invoice_id',
                'i.invoice_code',

                // Total quantity of items in invoice
                DB::raw('SUM(ii.quantity) as total_invoice_quantity'),

                // Total selling price of invoice (already calculated)
                DB::raw('SUM(ii.total_price) as invoice_total_sales'),

                // Total purchase cost of invoice
                DB::raw('
                    SUM(
                        ii.quantity * (p.purchase_price / NULLIF(p.stock, 0))
                    ) as invoice_total_purchase_cost
                '),

                // Invoice profit
                DB::raw('
                    SUM(ii.total_price)
                    -
                    SUM(
                        ii.quantity * (p.purchase_price / NULLIF(p.stock, 0))
                    ) as invoice_profit
                ')
            )
            ->groupBy(
                'i.id',
                'i.invoice_code'
            )
            ->orderByDesc('invoice_profit')
            ->get();
            $totalProfit = $profitReport->sum(function ($item) {
                return (float) $item->invoice_profit;
            });


        $top_selling_products = InvoiceItem::select(
                'item_name',
                DB::raw('SUM(quantity) as total_quantity_sold')
            )
            ->groupBy('item_name')
            ->orderByDesc('total_quantity_sold')
            ->limit(10)
            ->get();

        $tanggal_awal = date('Y-m-01');
        $tanggal_akhir = date('Y-m-d');

        $data_tanggal = array();
        $data_pendapatan = array();

        while (strtotime($tanggal_awal) <= strtotime($tanggal_akhir)) {
            $data_tanggal[] = (int) substr($tanggal_awal, 8, 2);

            $total_penjualan = Invoice::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('grand_total');
            // $total_pembelian = Pembelian::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('payment');
            $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('amount');

            $pendapatan = $total_penjualan - $total_pengeluaran;
            $data_pendapatan[] += $pendapatan;

            $tanggal_awal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal_awal)));
        }

        $tanggal_awal = date('Y-m-01');

        if (auth()->user()->access_level == 1) {
            return view('admin.dashboard', compact('kategori', 'produk', 'supplier', 'member', 'penjualan', 'pengeluaran', 'pembelian', 'tanggal_awal', 'tanggal_akhir', 'data_tanggal', 'data_pendapatan', 'total_invoice', 'top_selling_products', 'totalProfit', 'lowStockProducts'));
        } else {
            return view('kasir.dashboard');
        }
    }
}