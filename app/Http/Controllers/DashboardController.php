<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Member;
use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\ProductStock;
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
    // Basic counts
    $kategori   = Kategori::count();
    $produk     = Produk::count();
    $supplier   = Supplier::count();
    $member     = Member::count();

    // Totals
    $penjualan  = Invoice::sum('grand_total');
    $total_invoice = Invoice::all();
    $pengeluaran = Pengeluaran::sum('amount');
    $pembelian   = Produk::sum('purchase_price');

    // Low stock products
    $lowStockProducts = ProductStock::whereColumn('stock', '<=', 'minimum_stock')
        ->with('product')
        ->get();

    // Profit report (join product_stocks for stock info)
    $profitReport = DB::table('invoice_items as ii')
        ->join('invoices as i', 'ii.invoice_id', '=', 'i.id')
        ->join('products as p', 'ii.item_id', '=', 'p.product_id')
        ->join('product_stocks as ps', 'p.product_id', '=', 'ps.product_id')
        ->select(
            'i.id as invoice_id',
            'i.invoice_code',
            DB::raw('SUM(ii.quantity) as total_invoice_quantity'),
            DB::raw('SUM(ii.total_price) as invoice_total_sales'),
            DB::raw('SUM(ii.quantity * p.purchase_price) as invoice_total_purchase_cost'),
            DB::raw('SUM(ii.total_price) - SUM(ii.quantity * p.purchase_price) as invoice_profit')
        )
        ->groupBy('i.id', 'i.invoice_code')
        ->orderByDesc('invoice_profit')
        ->get();

    $totalProfit = $profitReport->sum(function ($item) {
        return (float) $item->invoice_profit;
    });

    // Top selling products
    $top_selling_products = InvoiceItem::select(
            'item_name',
            DB::raw('SUM(quantity) as total_quantity_sold')
        )
        ->groupBy('item_name')
        ->orderByDesc('total_quantity_sold')
        ->limit(10)
        ->get();

    // Daily revenue vs expenses
    $tanggal_awal  = date('Y-m-01');
    $tanggal_akhir = date('Y-m-d');

    $data_tanggal    = [];
    $data_pendapatan = [];

    while (strtotime($tanggal_awal) <= strtotime($tanggal_akhir)) {
        $data_tanggal[] = (int) substr($tanggal_awal, 8, 2);

        $total_penjualan   = Invoice::whereDate('created_at', $tanggal_awal)->sum('grand_total');
        $total_pengeluaran = Pengeluaran::whereDate('created_at', $tanggal_awal)->sum('amount');

        $pendapatan = $total_penjualan - $total_pengeluaran;
        $data_pendapatan[] = $pendapatan;

        $tanggal_awal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal_awal)));
    }

    $tanggal_awal = date('Y-m-01');

    // Render view based on access level
    if (auth()->user()->access_level == 1) {
        return view('admin.dashboard', compact(
            'kategori',
            'produk',
            'supplier',
            'member',
            'penjualan',
            'pengeluaran',
            'pembelian',
            'tanggal_awal',
            'tanggal_akhir',
            'data_tanggal',
            'data_pendapatan',
            'total_invoice',
            'top_selling_products',
            'totalProfit',
            'lowStockProducts'
        ));
    } else {
        return view('kasir.dashboard');
    }
}

}