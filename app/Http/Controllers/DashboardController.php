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
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
{
    // Parse date range from request, fallback to current month
    $tanggal_awal = $request->input('date_start');
    $tanggal_akhir = $request->input('date_end');
    if (!$tanggal_awal || !$tanggal_akhir) {
        $tanggal_awal = date('Y-m-01');
        $tanggal_akhir = date('Y-m-d');
    }
    $tanggal_awal  = Carbon::parse($tanggal_awal)->startOfDay();
    $tanggal_akhir = Carbon::parse($tanggal_akhir)->endOfDay();

    // Basic counts
    $kategori   = Kategori::count();
    $produk     = Produk::count();
    $supplier   = Supplier::count();
    $member     = Member::count();

    // Totals
    $penjualan  = Invoice::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])->sum('grand_total');
    $total_invoice = Invoice::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])->get();
    $pengeluaran = Pengeluaran::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])->sum('amount');
    $pembelian   = Produk::sum('purchase_price');

    // Low stock products
    $lowStockProducts = ProductStock::with('product')->whereColumn('stock', '<=', 'minimum_stock')->get();

    // Profit report (join product_stocks for stock info)
    $profitReport = DB::table('invoice_items as ii')
        ->join('invoices as i', 'ii.invoice_id', '=', 'i.id')
        ->join('products as p', 'ii.item_id', '=', 'p.product_id')
        ->join('product_stocks as ps', 'p.product_id', '=', 'ps.product_id')
        ->whereBetween('i.created_at', [$tanggal_awal, $tanggal_akhir])
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
    $top_selling_products = InvoiceItem::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
        ->select(
            'item_name',
            DB::raw('SUM(quantity) as total_quantity_sold')
        )
        ->groupBy('item_name')
        ->orderByDesc('total_quantity_sold')
        ->limit(10)
        ->get();

    // Daily revenue vs expenses
    $data_tanggal    = [];
    $data_pendapatan = [];
    $date = $tanggal_awal;
    while (strtotime($date) <= strtotime($tanggal_akhir)) {
        $data_tanggal[] = date('d/m/Y', strtotime($date));
        $total_penjualan   = Invoice::whereDate('created_at', $date)->sum('grand_total');
        $total_pengeluaran = Pengeluaran::whereDate('created_at', $date)->sum('amount');
        $pendapatan = $total_penjualan - $total_pengeluaran;
        $data_pendapatan[] = $pendapatan;
        $date = date('Y-m-d', strtotime("+1 day", strtotime($date)));
    }

    // Calculate payment statistics
    $totalCashPaid = $total_invoice
        ->where('payment_received', 'cash')
        ->where('payment_status', 'paid')
        ->sum(function ($invoice) {
            return (float) ($invoice->grand_total ?? 0);
        });

    $totalAccountPaid = $total_invoice
        ->whereNotIn('payment_received', ['cash', 'other'])
        ->where('payment_status', 'paid')
        ->sum('grand_total');

    $totalPending = $total_invoice
        ->where('payment_status', 'partial')
        ->sum(function ($invoice) {
            return (float) ($invoice->remaining_amount ?? 0);
        });

    // Prepare statistics array with ALL data needed for UI
    $statistics = [
        'total_invoices' => $penjualan,
        'total_categories' => $kategori,
        'total_products' => $produk,
        'total_members' => $member,
        'total_suppliers' => $supplier,
        'total_sales' => $penjualan,
        'total_expenses' => $pengeluaran,
        'total_purchases' => $pembelian,
        'total_cash_paid' => $totalCashPaid,
        'total_account_paid' => $totalAccountPaid,
        'total_pending' => $totalPending,
        'total_profit' => $totalProfit,
    ];

    // If AJAX, return comprehensive JSON for dashboard update
    if ($request->ajax()) {
        return response()->json([
            'data_tanggal' => $data_tanggal,
            'data_pendapatan' => $data_pendapatan,
            'statistics' => $statistics,
            'top_selling_products' => $top_selling_products,
            'low_stock_products' => $lowStockProducts,
            'total_invoice' => $total_invoice,
            'penjualan' => $penjualan,
            'pengeluaran' => $pengeluaran,
            'pembelian' => $pembelian,
            'totalProfit' => $totalProfit,
            'kategori' => $kategori,
            'produk' => $produk,
            'supplier' => $supplier,
            'member' => $member,
        ]);
    }

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