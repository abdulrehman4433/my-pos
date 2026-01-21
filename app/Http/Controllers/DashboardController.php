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
        $profitReport = DB::table('invoice_items as ii')
    ->join('products as p', 'ii.item_id', '=', 'p.product_id')
    ->whereNotNull('ii.item_id')
    ->select(
        'p.product_id',
        'p.product_name',
        'p.purchase_price',
        'p.stock',
        
        // Total quantity sold
        DB::raw('SUM(ii.quantity) as total_sold_quantity'),
        
        // Total sales revenue
        DB::raw('SUM(ii.quantity * ii.per_item_price) as total_sales'),
        
        // Calculate cost per unit (total purchase price divided by current stock + sold quantity)
        // This assumes p.stock is CURRENT stock (after sales)
        DB::raw('
            CASE 
                WHEN (p.stock + SUM(ii.quantity)) > 0 
                THEN (p.purchase_price / (p.stock + SUM(ii.quantity)))
                ELSE 0 
            END as cost_per_unit
        '),
        
        // Total cost: sold quantity × cost per unit
        DB::raw('
            SUM(ii.quantity) * 
            CASE 
                WHEN (p.stock + SUM(ii.quantity)) > 0 
                THEN (p.purchase_price / (p.stock + SUM(ii.quantity)))
                ELSE 0 
            END as total_cost
        '),
        
        // Profit: sales - cost
        DB::raw('
            SUM(ii.quantity * ii.per_item_price)
            - 
            (
                SUM(ii.quantity) * 
                CASE 
                    WHEN (p.stock + SUM(ii.quantity)) > 0 
                    THEN (p.purchase_price / (p.stock + SUM(ii.quantity)))
                    ELSE 0 
                END
            ) as profit
        ')
    )
    ->groupBy('p.product_id', 'p.product_name', 'p.purchase_price', 'p.stock')
    ->orderBy('profit', 'desc')
    ->get();

            // dd($profitReport);
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
            return view('admin.dashboard', compact('kategori', 'produk', 'supplier', 'member', 'penjualan', 'pengeluaran', 'pembelian', 'tanggal_awal', 'tanggal_akhir', 'data_tanggal', 'data_pendapatan', 'total_invoice', 'top_selling_products', 'lowStockProducts', 'profitReport'));
        } else {
            return view('kasir.dashboard');
        }
    }
}