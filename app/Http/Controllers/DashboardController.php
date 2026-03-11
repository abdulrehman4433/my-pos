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

        // Master data counts filtered by created_at date range
        $kategori = Kategori::count();
        $produk   = Produk::count();
        $supplier = Supplier::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])->count();
        $member = Member::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])->count();

        // Get invoices for the date range (only paid invoices for financial calculations)
        $paidInvoices = Invoice::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->where('payment_status', 'paid')
            ->whereNotIn('return_status', ['full'])
            ->get();

        // Get all invoices for the date range (including unpaid)
        $allInvoices = Invoice::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->get();

        // Total sales (grand total of all invoices in date range)
        $penjualan = $allInvoices->sum('grand_total');

        // Sales amount (only paid invoices, not fully returned)
        $sales_amount = $paidInvoices->sum('grand_total');
        
        // Total invoices collection
        $total_invoice = $allInvoices;
        
        // Total expenses in date range
        $pengeluaran = Pengeluaran::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->sum('amount');
            
        // Total purchases calculation (cost of goods sold during date range)
        $pembelian = DB::table('invoice_items as ii')
            ->join('invoices as i', 'ii.invoice_id', '=', 'i.id')
            ->join('products as p', 'ii.item_id', '=', 'p.product_id')
            // ->whereBetween('i.created_at', [$tanggal_awal, $tanggal_akhir])
            ->where('i.payment_status', 'paid')
            ->where('i.return_status', '!=', 'full')
            ->selectRaw('SUM(ii.quantity * p.purchase_price) as total_purchase_cost')
            ->value('total_purchase_cost');

        // Low stock products (filter by when stock was recorded/updated within date range)
        $lowStockProducts = ProductStock::with('product')
            ->whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->get();

        // Profit report with detailed breakdown
        $profitReport = DB::table('invoice_items as ii')
            ->join('invoices as i', 'ii.invoice_id', '=', 'i.id')
            ->join('products as p', 'ii.item_id', '=', 'p.product_id')
            ->whereBetween('i.created_at', [$tanggal_awal, $tanggal_akhir])
            ->where('i.payment_status', 'paid')
            ->where('i.return_status', '!=', 'full')
            ->select(
                'i.id as invoice_id',
                'i.invoice_code',
                'i.discount_amount',
                DB::raw('SUM(ii.quantity) as total_invoice_quantity'),
                DB::raw('SUM(ii.per_item_price * ii.quantity) as gross_sales'),
                DB::raw('SUM(ii.quantity * p.purchase_price) as total_purchase_cost'),
                DB::raw('
                    SUM(ii.per_item_price * ii.quantity) - 
                    SUM(ii.quantity * p.purchase_price) as profit_before_discount
                ')
            )
            ->groupBy('i.id', 'i.invoice_code', 'i.discount_amount')
            ->get();

        // Calculate total profit considering discounts
        $totalProfit = $profitReport->sum(function($item) {
            $profitBeforeDiscount = (float) $item->profit_before_discount;
            $discountPercent = (float) $item->discount_amount;
            return $profitBeforeDiscount * (1 - ($discountPercent / 100));
        });

        // Top selling products within date range
        $top_selling_products = InvoiceItem::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->select(
                'item_name',
                DB::raw('SUM(quantity) as total_quantity_sold'),
                DB::raw('SUM(per_item_price * quantity) as total_revenue')
            )
            ->groupBy('item_name')
            ->orderByDesc('total_quantity_sold')
            ->limit(10)
            ->get();

        // Daily revenue vs expenses
        $data_tanggal = [];
        $data_pendapatan = [];
        $currentDate = clone $tanggal_awal;
        
        while ($currentDate <= $tanggal_akhir) {
            $dateStr = $currentDate->format('Y-m-d');
            $data_tanggal[] = $currentDate->format('d/m/Y');
            
            // Get total sales for the day (paid invoices only)
            $total_penjualan = Invoice::whereDate('created_at', $dateStr)
                ->where('payment_status', 'paid')
                ->whereNotIn('return_status', ['full'])
                ->sum('grand_total');
            
            // Get total expenses for the day
            $total_pengeluaran = Pengeluaran::whereDate('created_at', $dateStr)
                ->sum('amount');
            
            // Calculate net income for the day
            $pendapatan = $total_penjualan - $total_pengeluaran;
            
            $data_pendapatan[] = $pendapatan;
            $currentDate->addDay();
        }

        // Calculate payment statistics
        $totalCashPaid = $allInvoices
            ->where('payment_received', 'cash')
            ->where('payment_status', 'paid')
            ->whereNotIn('return_status', ['full'])
            ->sum(function ($invoice) {
                return (float) ($invoice->grand_total ?? 0);
            });

        $totalAccountPaid = $allInvoices
            ->whereNotIn('payment_received', ['cash', 'other'])
            ->where('payment_status', 'paid')
            ->whereNotIn('return_status', ['full'])
            ->sum('grand_total');

        $totalPending = $allInvoices
            ->where('payment_status', 'partial')
            ->whereNotIn('return_status', ['full'])
            ->sum(function ($invoice) {
                return (float) ($invoice->remaining_amount ?? 0);
            });

        // Get newly created records counts for the date range
        $newCategories = Kategori::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])->count();
        $newProducts = Produk::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])->count();
        $newSuppliers = Supplier::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])->count();
        $newMembers = Member::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])->count();

        // Prepare statistics array with ALL data needed for UI
        $statistics = [
            'total_invoices' => $penjualan,
            'total_categories' => $kategori,
            'total_products' => $produk,
            'total_members' => $member,
            'total_suppliers' => $supplier,
            'total_sales' => $sales_amount,
            'total_expenses' => $pengeluaran,
            'total_purchases' => $pembelian ?? 0,
            'total_cash_paid' => $totalCashPaid,
            'total_account_paid' => $totalAccountPaid,
            'total_pending' => $totalPending,
            'total_profit' => $totalProfit,
            'new_categories' => $newCategories,
            'new_products' => $newProducts,
            'new_suppliers' => $newSuppliers,
            'new_members' => $newMembers,
        ];

        // If AJAX, return comprehensive JSON for dashboard update
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data_tanggal' => $data_tanggal,
                'data_pendapatan' => $data_pendapatan,
                'statistics' => $statistics,
                'top_selling_products' => $top_selling_products,
                'low_stock_products' => $lowStockProducts,
                'total_invoice' => $allInvoices,
                'penjualan' => $penjualan,
                'sales_amount' => $sales_amount,
                'pengeluaran' => $pengeluaran,
                'pembelian' => $pembelian ?? 0,
                'totalProfit' => $totalProfit,
                'kategori' => $kategori,
                'produk' => $produk,
                'supplier' => $supplier,
                'member' => $member,
                'new_categories' => $newCategories,
                'new_products' => $newProducts,
                'new_suppliers' => $newSuppliers,
                'new_members' => $newMembers,
                'date_range' => [
                    'start' => $tanggal_awal->format('Y-m-d'),
                    'end' => $tanggal_akhir->format('Y-m-d')
                ]
            ]);
        }

        // For non-AJAX requests, pass all variables to the view
        $viewData = [
            'kategori' => $kategori,
            'produk' => $produk,
            'supplier' => $supplier,
            'member' => $member,
            'new_categories' => $newCategories,
            'new_products' => $newProducts,
            'new_suppliers' => $newSuppliers,
            'new_members' => $newMembers,
            'penjualan' => $penjualan,
            'sales_amount' => $sales_amount,
            'pengeluaran' => $pengeluaran,
            'pembelian' => $pembelian ?? 0,
            'tanggal_awal' => $tanggal_awal->format('Y-m-d'),
            'tanggal_akhir' => $tanggal_akhir->format('Y-m-d'),
            'data_tanggal' => $data_tanggal,
            'data_pendapatan' => $data_pendapatan,
            'total_invoice' => $allInvoices,
            'top_selling_products' => $top_selling_products,
            'totalProfit' => $totalProfit,
            'lowStockProducts' => $lowStockProducts,
            'totalCashPaid' => $totalCashPaid,
            'totalAccountPaid' => $totalAccountPaid,
            'totalPending' => $totalPending,
        ];

        // Render view based on access level
        if (auth()->user()->access_level == 1) {
            return view('admin.dashboard', $viewData);
        } else {
            return view('kasir.dashboard', $viewData);
        }
    }
}