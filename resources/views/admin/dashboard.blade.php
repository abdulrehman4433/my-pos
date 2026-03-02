@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Dashboard</li>
@endsection

@section('content')
<!-- Date Range Filter -->
<div class="row" style="margin-bottom: 10px;">
    <div class="col-lg-12 text-right">
        <form id="dashboard-date-range-form" class="form-inline" style="display:inline-block;">
            <div class="form-group">
                <label for="dashboard_date_start" style="font-size: 18px;">Date Range: </label>
                <input type="date" id="dashboard_date_start" name="date_start" class="form-control" style="font-size:18px; height:44px; width:180px;" value="{{ request('date_start', $tanggal_awal) }}">
                <span style="font-size: 18px; margin: 0 8px;">to</span>
                <input type="date" id="dashboard_date_end" name="date_end" class="form-control" style="font-size:18px; height:44px; width:180px;" value="{{ request('date_end', $tanggal_akhir) }}">
            </div>
            <button type="submit" class="btn btn-primary" style="font-size:18px; height:44px; min-width:100px; margin-left:8px;">Filter</button>
            <button type="button" id="reset-date-filter" class="btn btn-default" style="font-size:18px; height:44px; min-width:100px; margin-left:8px;">Reset to Current Month</button>
        </form>
    </div>
</div>

<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green" onclick="addForm('{{ route('invoice.store') }}')">
            <div class="inner">
                <h3 id="total-invoices">{{ $penjualan ?? 0 }}</h3>
                <p class="p-20">Create Invoice</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('kategori.index') }}">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 id="total-categories">{{ $kategori ?? 0 }}</h3>
                <p class="p-20">Total Categories</p>
            </div>
            <div class="icon">
                <i class="fa fa-cube"></i>
            </div>
        </div>
        </a>
    </div>
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('produk.index') }}">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3 id="total-products">{{ $produk ?? 0 }}</h3>
                <p class="p-20">Total Product</p>
            </div>
            <div class="icon">
                <i class="fa fa-cubes"></i>
            </div>
        </div>
        </a>
    </div>
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('member.index') }}">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3 id="total-members">{{ $member ?? 0 }}</h3>
                <p class="p-20">Total Worker</p>
            </div>
            <div class="icon">
                <i class="fa fa-id-card"></i>
            </div>
        </div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('supplier.index') }}">
        <div class="small-box bg-olive">
            <div class="inner">
                <h3 id="total-suppliers">{{ $supplier ?? 0 }}</h3>
                <p class="p-20">Total Supplier</p>
            </div>
            <div class="icon">
                <i class="fa fa-truck"></i>
            </div>
        </div>
        </a>
    </div>
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('penjualan.index') }}">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3 id="total-sales">{{ $sales_amount ?? 0 }}</h3>
                <p class="p-20">Sales</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('pengeluaran.index') }}">
        <div class="small-box bg-red">
            <div class="inner">
                <h3 id="total-expenses">{{ $pengeluaran ?? 0 }}</h3>
                <p class="p-20">Total Expenses</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('pembelian.index') }}">
        <div class="small-box bg-green">
            <div class="inner">
                <h3 id="total-purchases">{{ $pembelian ?? 0 }}</h3>
                <p class="p-20">Total Purchase</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="#">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 id="total-cash-paid">{{ $totalCashPaid ?? 0 }}</h3>
                <p class="p-20">Total Cash Amount Received</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="#">
        <div class="small-box bg-olive">
            <div class="inner">
                <h3 id="total-account-paid">{{ $totalAccountPaid ?? 0 }}</h3>
                <p class="p-20">Amount Received In Bank Account</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('partial-transaction-return.data') }}">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3 id="total-pending">{{ $totalPending ?? 0 }}</h3>
                <p class="p-20">Total Pending Payment</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>

    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="#">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3 id="total-profit">{{ $totalProfit ?? 0 }}</h3>
                <p class="p-20">Total Profit</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- Main row -->
<div class="row">
    <div class="col-lg-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title" id="chart-title">Income Chart {{ isset($tanggal_awal) ? tanggal_indonesia($tanggal_awal, false) : '' }} - {{ isset($tanggal_akhir) ? tanggal_indonesia($tanggal_akhir, false) : '' }}</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="chart">
                            <canvas id="salesChart" style="height: 280px; width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="box box-primary" style="height: 345px; min-height: 345px !important; overflow-y: auto;">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-line-chart"></i> Top Selling Products
                </h3>
            </div>
            <div class="box-body">
                <ul class="products-list product-list-in-box" id="top-selling-products-list">
                    @forelse ($top_selling_products ?? [] as $index => $product)
                        <li class="item">
                            <div class="product-info">
                                <span class="product-title">
                                    @if ($index == 0) 🥇
                                    @elseif ($index == 1) 🥈
                                    @elseif ($index == 2) 🥉
                                    @else #{{ $index + 1 }}
                                    @endif

                                    {{ $product->item_name ?? $product->product_name ?? 'N/A' }}

                                    <span class="label label-success pull-right">
                                        {{ $product->total_quantity_sold ?? 0 }} sold
                                    </span>
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="item">
                            <div class="product-info">
                                <span class="product-title">No sales data available</span>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Main row -->
<div class="row">
    <div class="col-lg-6"> 
        <div class="box box-primary" style="height: 300px; min-height: 300px !important; overflow-y: auto;">
            <div class="box-header with-border">
                <h3 class="box-title">Products going out of stock soon</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Stock</th>
                            <th>Minimum Stock</th>
                        </tr>
                    </thead>
                    <tbody id="low-stock-products-table">
                        @forelse ($lowStockProducts ?? [] as $product)
                        <tr>
                            <td>{{ $product->product->product_name ?? $product->product->product_name ?? 'N/A' }}</td>
                            <td>{{ $product->stock ?? 0 }}</td>
                            <td>{{ $product->minimum_stock ?? 0 }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">No low stock products</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('invoice.form')
@endsection

@push('css')
    <style>
        .products-list .item {
            padding: 12px 10px;
            border-bottom: 1px solid #f4f4f4;
            transition: background 0.3s ease;
        }

        .products-list .item:hover {
            background: #f9f9f9;
        }

        .product-title {
            font-size: 15px;
            font-weight: 600;
        }

        .label {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 12px;
        }
        
        .p-20 {
            font-size: 20px !important;
        }
        
        .small-box {
            padding: 10px 0px;
            cursor: pointer;
        }
        
        #product_select, #product_select option {
            color: #222 !important;
            background-color: #fff !important;
        }
        
        .select2-container .select2-selection--single {
            height: 40px !important;
            min-height: 35px !important;
            line-height: 35px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 35px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px !important;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            color: #fff !important;
        }
        
        .chart {
            position: relative;
            height: 280px;
            width: 100%;
        }
    </style>
@endpush

@push('scripts')
<!-- ChartJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script>
// Global variables
let salesChart = null;
let productStockMap = {};

// Local Storage Keys
const STORAGE_KEYS = {
    DATE_START: 'dashboard_date_start',
    DATE_END: 'dashboard_date_end'
};

// Error handler for debugging
window.onerror = function(msg, url, line, col, error) {
    console.error('Global Error: ' + msg + ' at ' + url + ':' + line);
    return false;
};

// Format date as d/m/Y
function formatDateDMY(dateStr) {
    if (!dateStr) return '';
    
    // Try Y-m-d
    let match = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (match) return `${match[3]}/${match[2]}/${match[1]}`;
    
    // Try d-m-Y
    match = dateStr.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (match) return `${match[1]}/${match[2]}/${match[3]}`;
    
    // Try d/m/Y
    match = dateStr.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (match) return `${match[1]}/${match[2]}/${match[3]}`;
    
    // Try native Date
    const d = new Date(dateStr);
    if (!isNaN(d)) {
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }
    
    return dateStr;
}

// Get current month dates in YYYY-MM-DD format
function getCurrentMonthDates() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const firstDay = `${year}-${month}-01`;
    
    // Get last day of month
    const lastDay = new Date(year, now.getMonth() + 1, 0);
    const lastDayFormatted = `${year}-${month}-${String(lastDay.getDate()).padStart(2, '0')}`;
    
    return {
        start: firstDay,
        end: lastDayFormatted
    };
}

// Load saved dates from local storage
function loadSavedDates() {
    const savedStart = localStorage.getItem(STORAGE_KEYS.DATE_START);
    const savedEnd = localStorage.getItem(STORAGE_KEYS.DATE_END);
    
    if (savedStart && savedEnd) {
        return { start: savedStart, end: savedEnd };
    }
    
    return null;
}

// Save dates to local storage
function saveDatesToStorage(start, end) {
    localStorage.setItem(STORAGE_KEYS.DATE_START, start);
    localStorage.setItem(STORAGE_KEYS.DATE_END, end);
}

// Clear dates from local storage
function clearSavedDates() {
    localStorage.removeItem(STORAGE_KEYS.DATE_START);
    localStorage.removeItem(STORAGE_KEYS.DATE_END);
}

// Update date inputs with values
function updateDateInputs(start, end) {
    $('#dashboard_date_start').val(start);
    $('#dashboard_date_end').val(end);
}

// Load dashboard data with specified dates
function loadDashboardData(start, end, updateStorage = true) {
    // console.log('Loading dashboard data for:', start, 'to', end);
    
    if (updateStorage) {
        saveDatesToStorage(start, end);
    }
    
    // Show loading state
    const $btn = $('#dashboard-date-range-form').find('button[type="submit"]');
    const originalText = $btn.text();
    $btn.text('Loading...').prop('disabled', true);
    
    // Get CSRF token
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Make AJAX request
    $.ajax({
        url: '{{ route("dashboard") }}',
        type: 'GET',
        data: { 
            date_start: start, 
            date_end: end 
        },
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        success: function(res) {
            // console.log('Dashboard data loaded successfully');
            
            // Update chart
            const labels = res.data_tanggal || [];
            const data = res.data_pendapatan || [];
            renderSalesChart(labels, data);
            
            // Update chart header
            updateChartHeaderDateRange(start, end);
            
            // Update all statistics
            updateAllStatistics(res);
            
            // console.log('Dashboard updated successfully');
        },
        error: function(xhr, status, error) {
            console.error('Error loading dashboard data:', error);
            
            // Show error on chart
            const canvas = document.getElementById('salesChart');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                destroyChart();
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.font = '16px Arial';
                ctx.fillStyle = '#f00';
                ctx.textAlign = 'center';
                ctx.fillText('Error loading data', canvas.width/2, canvas.height/2);
            }
            
            alert('Failed to load dashboard data. Please try again.');
        },
        complete: function() {
            $btn.text(originalText).prop('disabled', false);
        }
    });
}

// Update all statistics on the page
function updateAllStatistics(res) {
    // Update basic counts
    $('#total-invoices').text(formatNumber(res.penjualan || 0));
    $('#total-categories').text(formatNumber(res.kategori || 0));
    $('#total-products').text(formatNumber(res.produk || 0));
    $('#total-members').text(formatNumber(res.member || 0));
    $('#total-suppliers').text(formatNumber(res.supplier || 0));
    $('#total-sales').text(formatNumber(res.sales_amount || 0));
    $('#total-expenses').text(formatNumber(res.pengeluaran || 0));
    $('#total-purchases').text(formatNumber(res.pembelian || 0));
    
    // Update payment statistics
    if (res.statistics) {
        $('#total-cash-paid').text(formatNumber(res.statistics.total_cash_paid || 0));
        $('#total-account-paid').text(formatNumber(res.statistics.total_account_paid || 0));
        $('#total-pending').text(formatNumber(res.statistics.total_pending || 0));
        $('#total-profit').text(formatNumber(res.statistics.total_profit || 0));
    }
    
    // Update top selling products
    if (res.top_selling_products) {
        updateTopSellingProducts(res.top_selling_products);
    }
    
    // Update low stock products
    if (res.low_stock_products) {
        updateLowStockProducts(res.low_stock_products);
    }
}

function destroyChart() {
    if (salesChart) {
        try {
            if (typeof salesChart.destroy === 'function') {
                salesChart.destroy();
            }
        } catch (e) {
            console.warn('Error destroying chart:', e);
        } finally {
            salesChart = null;
        }
    }
}

// ============== IMPROVED CHART FUNCTIONS ==============

function renderSalesChart(labels, data) {
    // console.log('Rendering chart with:', labels, data);
    
    const canvas = document.getElementById('salesChart');
    if (!canvas) {
        console.error('Canvas element not found');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Safely destroy existing chart
    destroyChart();
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Handle empty data
    if (!labels || !data || !Array.isArray(labels) || !Array.isArray(data) || labels.length === 0) {
        // console.log('No chart data available');
        ctx.font = '16px Arial';
        ctx.fillStyle = '#999';
        ctx.textAlign = 'center';
        ctx.fillText('No data available for selected date range', canvas.width/2, canvas.height/2);
        return;
    }
    
    // Format labels
    const formattedLabels = labels.map(l => formatDateDMY(l));
    
    // Ensure data is numeric
    const numericData = data.map(val => {
        const num = parseFloat(val);
        return isNaN(num) ? 0 : num;
    });
    
    try {
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: formattedLabels,
                datasets: [{
                    label: 'Revenue',
                    backgroundColor: 'rgba(60,141,188,0.2)',
                    borderColor: 'rgba(60,141,188,0.9)',
                    pointBackgroundColor: '#3b8bba',
                    pointBorderColor: 'rgba(60,141,188,1)',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(60,141,188,1)',
                    data: numericData,
                    fill: true,
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += formatCurrency(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
        // console.log('Chart rendered successfully');
    } catch (e) {
        console.error('Error creating chart:', e);
        ctx.font = '16px Arial';
        ctx.fillStyle = '#f00';
        ctx.textAlign = 'center';
        ctx.fillText('Error loading chart', canvas.width/2, canvas.height/2);
    }
}

function formatCurrency(value) {
    if (value === null || value === undefined || isNaN(value)) {
        value = 0;
    }
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) {
        return '0';
    }
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function updateChartHeaderDateRange(start, end) {
    if (!start || !end) return;
    
    const formattedStart = formatDateDMY(start);
    const formattedEnd = formatDateDMY(end);
    
    $('#chart-title').text(`Income Chart ${formattedStart} - ${formattedEnd}`);
}

function updateTopSellingProducts(products) {
    const $container = $('#top-selling-products-list');
    if (!$container.length) return;
    
    $container.empty();
    
    if (!products || products.length === 0) {
        $container.append(`
            <li class="item">
                <div class="product-info">
                    <span class="product-title">No sales data available</span>
                </div>
            </li>
        `);
        return;
    }
    
    products.forEach((product, index) => {
        let medal = '';
        if (index === 0) medal = '🥇';
        else if (index === 1) medal = '🥈';
        else if (index === 2) medal = '🥉';
        else medal = `#${index + 1}`;
        
        const productName = product.item_name || product.product_name || 'N/A';
        const quantitySold = product.total_quantity_sold || 0;
        
        $container.append(`
            <li class="item">
                <div class="product-info">
                    <span class="product-title">
                        ${medal} ${escapeHtml(productName)}
                        <span class="label label-success pull-right">
                            ${quantitySold} sold
                        </span>
                    </span>
                </div>
            </li>
        `);
    });
}

function updateLowStockProducts(products) {
    const $tbody = $('#low-stock-products-table');
    if (!$tbody.length) return;
    
    $tbody.empty();
    
    if (!products || products.length === 0) {
        $tbody.append(`
            <tr>
                <td colspan="3" class="text-center">No low stock products</td>
            </tr>
        `);
        return;
    }
    
    products.forEach(product => {
        const productName = product.product.product_name || product.product.name || 'N/A';
        const stock = product.stock || 0;
        const minStock = product.minimum_stock || 0;
        
        $tbody.append(`
            <tr>
                <td>${escapeHtml(productName)}</td>
                <td>${stock}</td>
                <td>${minStock}</td>
            </tr>
        `);
    });
}

function escapeHtml(text) {
    if (!text) return text;
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// ============== IMPROVED INITIALIZATION ==============

// Initialize when document is ready
$(document).ready(function() {
    // console.log('Document ready - Initializing dashboard');
    
    // Check Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded!');
    } else {
        // console.log('Chart.js version:', Chart.version);
    }
    
    // Get chart data from Blade
    let chartLabels = [];
    let chartData = [];
    
    @if(isset($data_tanggal))
        chartLabels = @json($data_tanggal);
        // console.log('Chart labels from server:', chartLabels);
    @endif
    
    @if(isset($data_pendapatan))
        chartData = @json($data_pendapatan);
        // console.log('Chart data from server:', chartData);
    @endif
    
    // Check for saved dates in local storage
    const savedDates = loadSavedDates();
    
    if (savedDates) {
        // console.log('Found saved dates in local storage:', savedDates);
        updateDateInputs(savedDates.start, savedDates.end);
        loadDashboardData(savedDates.start, savedDates.end, false);
    } else {
        // console.log('No saved dates found, using server-provided dates');
        
        // Render chart with initial data
        setTimeout(() => {
            renderSalesChart(chartLabels, chartData);
            
            // Update chart header
            const start = $('#dashboard_date_start').val();
            const end = $('#dashboard_date_end').val();
            if (start && end) {
                updateChartHeaderDateRange(start, end);
                saveDatesToStorage(start, end);
            }
        }, 100);
    }

    // Date range filter form submit handler
    $('#dashboard-date-range-form').on('submit', function(e) {
        e.preventDefault();
        
        const start = $('#dashboard_date_start').val();
        const end = $('#dashboard_date_end').val();
        
        // console.log('Filter submitted - Start:', start, 'End:', end);
        
        // Validate dates
        if (!start || !end) {
            alert('Please select both start and end dates');
            return;
        }
        
        if (start > end) {
            alert('Start date cannot be after end date');
            return;
        }
        
        // Load dashboard data with new dates
        loadDashboardData(start, end, true);
    });

    // Reset button handler
    $('#reset-date-filter').on('click', function() {
        // console.log('Resetting to current month');
        
        // Get current month dates
        const currentMonth = getCurrentMonthDates();
        
        // Clear saved dates from storage
        clearSavedDates();
        
        // Update date inputs
        updateDateInputs(currentMonth.start, currentMonth.end);
        
        // Load dashboard data with current month dates
        loadDashboardData(currentMonth.start, currentMonth.end, true);
    });

    // Payment status change handler
    $(document).on('change', '#payment_status', function () {
        const status = $(this).val();
        const $receivedAmountGroup = $('#received_amount_group');

        if (status === 'partial') {
            $receivedAmountGroup.show();
        } else {
            $receivedAmountGroup.hide();
            $('input[name="received_amount"]').val(0);
        }
    });

    // Quantity input handler
    $(document).on('input', '.qty-input', function () {
        const row = $(this).closest('tr');
        const maxStock = parseInt($(this).data('max-stock')) || 0;
        let qty = parseInt($(this).val()) || 1;

        if (qty > maxStock) {
            qty = maxStock;
            $(this).val(qty);
            alert(`Maximum available stock is ${maxStock}`);
        }

        const price = parseFloat(row.find('input[name$="[price]"]').val()) || 0;
        const lineTotal = (price * qty).toFixed(2);
        row.find('.line-total').text(lineTotal);
        updateSubTotal();
    });

    // Remove row handler
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        updateSubTotal();
    });

    // Resource ID change handler
    $(document).on('change', '#resource_id', function () {
        const discount = $(this).find(':selected').data('discount');
        $('#discount_amount').val(discount ?? 0);
    });
});

// Keep all your existing invoice-related functions below this line exactly as they were
// addForm, generateInvoiceCode, referenceChanged, loadSelect2, createProductSelect, 
// addProductRow, updateSubTotal, editForm, deleteData, viewForm, viewFormDownload, invoiceResource

function addForm(url) {
    const $modal = $('#modal-form');
    $modal.modal('show');
    $modal.find('.modal-title').text('Add Invoice');

    const $form = $modal.find('form');
    $form[0].reset();
    $form.attr('action', url);
    $form.find('[name=_method]').val('post');
    $('#products_table tbody').empty();
    
    let $sourceInput = $form.find('input[name="from_dashboard"]');
    if ($sourceInput.length === 0) {
        $form.append('<input type="hidden" name="from_dashboard" value="1">');
    } else {
        $sourceInput.val('1');
    }

    const code = generateInvoiceCode();
    $form.find('#invoice_code').val(formatInvoiceCode(code, 'INV-'));
    $form.find('[name=invoice_reference]').focus();
}

function generateInvoiceCode() {
    const array = new Uint32Array(1);
    window.crypto.getRandomValues(array);
    const num = array[0] % 1_000_000;
    return String(num).padStart(6, '0');
}

function formatInvoiceCode(code, prefix = '') {
    return prefix ? `${prefix}${code}` : code;
}

function referenceChanged(select) {
    const value = select.value;
    const referenceGroup = $('#reference_id_group');
    const referenceContainer = $('#reference_id_container');
    const subTotalInput = $('input[name="sub_total"]');

    subTotalInput.val(0);
    referenceContainer.empty();
    productStockMap = {};

    if (value === 'product') {
        $('#quantity_group').hide();
        $('#sub_total_group').hide();
        $('#products_table_group').show();
        subTotalInput.prop('readonly', true);

        $.get('{{ route("invoice.product") }}')
            .done(res => {
                if (typeof $.fn.select2 === 'undefined') {
                    loadSelect2(() => createProductSelect(res));
                } else {
                    createProductSelect(res);
                }
            })
            .fail(() => {
                alert('Failed to load products');
                referenceContainer.html('<span class="text-danger">Failed to load products</span>');
            });
    } else {
        $('#quantity_group').show();
        $('#sub_total_group').show();
        $('#products_table_group').hide();
        referenceGroup.hide();
        referenceContainer.html('<input type="hidden" name="reference_id">');
        $('input[name="sub_total"]').prop('readonly', false).val(0);
        subTotalInput.prop('readonly', false);
    }
}

function loadSelect2(callback) {
    if ($('#select2-css').length === 0) {
        $('head').append('<link id="select2-css" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />');
    }
    
    if (typeof $.fn.select2 === 'undefined') {
        $.getScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', callback);
    } else {
        callback();
    }
}

function createProductSelect(res) {
    if (!res || !Array.isArray(res) || res.length === 0) {
        $('#reference_id_container').html('<span class="text-warning">No products available</span>');
        return;
    }
    
    let selectEl = $('<select>', {
        class: 'form-control',
        id: 'product_select',
        style: 'width:100%'
    });

    selectEl.append('<option value="">Select Product</option>');

    res.forEach(item => {
        const availableStock = item.stock ? (item.stock.stock || 0) : 0;
        productStockMap[item.product_id] = { availableStock };
        
        selectEl.append(`
            <option value="${item.product_id}"
                data-name="${item.product_name || ''}"
                data-price="${item.selling_price || 0}"
                data-stock="${availableStock}"
                data-code="${item.product_code || ''}"
                data-unit="${item.unit || ''}"
                data-brand="${item.brand || ''}"
                data-variant="${item.variant || ''}">
                ${item.product_name || 'Unknown'} (RS ${item.selling_price}) - Stock: ${availableStock}
            </option>
        `);
    });

    $('#reference_id_container').append(selectEl);
    $('#reference_id_group').show();

    selectEl.select2({
        dropdownParent: $('#reference_id_group'),
        placeholder: 'Select Product',
        allowClear: true,
        width: 'resolve'
    });

    selectEl.on('select2:select', function (e) {
        const opt = $(this).find(':selected');
        if (!opt.val()) return;

        const productId = opt.val();
        const availableStock = parseInt(opt.data('stock')) || 0;
        const requestedQty = parseInt($('#temp_quantity').val()) || 1;

        if (requestedQty > availableStock) {
            alert(`Insufficient stock!\n\nProduct: ${opt.data('name')}\nAvailable: ${availableStock}\nRequested: ${requestedQty}`);
            $(this).val('').trigger('change');
            return;
        }

        const product = {
            id: productId,
            code: opt.data('code') || '',
            name: opt.data('name'),
            price: Number(opt.data('price')) || 0,
            qty: requestedQty,
            availableStock: availableStock,
            unit: opt.data('unit') || '',
            brand: opt.data('brand') || '',
            variant: opt.data('variant') || ''
        };

        addProductRow(product);
        $(this).val('').trigger('change');
    });
}

function addProductRow(product) {
    product.price = product.price || 0;
    product.qty = product.qty || 1;
    product.availableStock = product.availableStock || 0;
    
    const existingRow = $(`#products_table tbody tr[data-id="${product.id}"]`);
    
    if (existingRow.length > 0) {
        const qtyInput = existingRow.find('.qty-input');
        const currentQty = parseInt(qtyInput.val()) || 1;
        const newQty = currentQty + product.qty;

        if (newQty > product.availableStock) {
            alert(`Cannot add more!\n\nProduct: ${product.name}\nAvailable Stock: ${product.availableStock}\nCurrent Qty: ${currentQty}\nRequested Add: ${product.qty}\nTotal Would Be: ${newQty}`);
            return;
        }

        qtyInput.val(newQty).trigger('input');
        return;
    }

    const rowTotal = product.price * product.qty;
    const row = `
        <tr data-id="${product.id}" data-stock="${product.availableStock}">
            <td>${escapeHtml(product.code) || 'N/A'}</td>
            <td>${escapeHtml(product.name) || 'N/A'}
                <input type="hidden" name="products[${product.id}][id]" value="${product.id}">
            </td>
            <td>${escapeHtml(product.brand) || 'N/A'}</td>
            <td>${escapeHtml(product.variant) || 'N/A'}</td>
            <td>${escapeHtml(product.unit) || 'N/A'}</td>
            <td>${formatCurrency(product.price)}
                <input type="hidden" name="products[${product.id}][price]" value="${product.price}">
            </td>
            <td>
                <input type="number" min="1" max="${product.availableStock}" value="${product.qty}"
                    name="products[${product.id}][qty]"
                    class="form-control qty-input"
                    data-max-stock="${product.availableStock}">
            </td>
            <td class="line-total">${rowTotal.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-xs remove-row">×</button>
            </td>
        </tr>
    `;

    $('#products_table tbody').append(row);
    updateSubTotal();
}

function updateSubTotal() {
    let total = 0;
    $('#products_table tbody tr').each(function () {
        total += parseFloat($(this).find('.line-total').text()) || 0;
    });
    $('input[name="sub_total"]').val(total.toFixed(2));
    $('#table-total').text(total.toFixed(2));
}

function editForm(url) {
    $('#modal-form').modal('show');
    $('#modal-form .modal-title').text('Edit Invoice');
    $('#modal-form form')[0].reset();
    $('#modal-form form').attr('action', url);
    $('#modal-form [name=_method]').val('put');

    $.get(url)
        .done((res) => {
            $('#modal-form [name=invoice_reference]').val(res.invoice_reference || '');
            $('#modal-form [name=reference_id]').val(res.reference_id || '');
            $('#modal-form [name=sub_total]').val(res.sub_total || 0);
            $('#modal-form [name=tax_amount]').val(res.tax_amount || 0);
            $('#modal-form [name=discount_amount]').val(res.discount_amount || 0);
            $('#modal-form [name=payment_received]').val(res.payment_received || '');
            $('#modal-form [name=payment_status]').val(res.payment_status || '');
        })
        .fail(() => {
            alert('Unable to display data');
        });
}

function deleteData(url) {
    if (confirm('Are you sure you want to delete this invoice?')) {
        $.post(url, {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            '_method': 'delete'
        })
        .done(() => {
            if (typeof table !== 'undefined' && table) {
                table.ajax.reload(null, false);
            }
        })
        .fail(() => {
            alert('Unable to delete data');
        });
    }
}

function viewForm(invoiceId) {
    const url = `/invoice/view/${invoiceId}`;
    window.open(url, '_blank');
}

function viewFormDownload(invoiceId) {
    const url = `/invoice/view/${invoiceId}?pdf=1`;
    const w = window.open(url, '_blank');
    if (w) {
        w.onload = function() {
            setTimeout(() => {
                w.print();
            }, 500);
        };
    }
}

function invoiceResource(select) {
    const value = select.value;
    const $group = $('#resource_id_group');
    const $container = $('#resource_id_container');

    $container.empty();
    $group.hide();

    if (value !== 'customer') return;

    $group.show();

    $.ajax({
        url: "{{ route('invoice.customer') }}",
        type: "GET",
        dataType: "json",
        success: function (res) {
            const $select = $('<select>', {
                class: 'form-control',
                id: 'resource_id',
                name: 'resource_id'
            });

            $select.append('<option value="">Select Customer</option>');

            if (Array.isArray(res) && res.length) {
                res.forEach(item => {
                    $select.append(
                        $('<option>', {
                            value: item.id,
                            text: item.name || 'Unknown',
                            'data-name': item.name,
                            'data-discount': item.discount || 0
                        })
                    );
                });
            } else {
                $select.append('<option value="">No customers found</option>');
            }

            $container.append($select);
        },
        error: function() {
            $container.append('<span class="text-danger">Failed to load customers</span>');
        }
    });
}

// Handle window resize
$(window).on('resize', function() {
    if (salesChart && typeof salesChart.resize === 'function') {
        salesChart.resize();
    }
});
</script>
@endpush