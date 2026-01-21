@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Dashboard</li>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $kategori }}</h3>

                <p>Total Categories</p>
            </div>
            <div class="icon">
                <i class="fa fa-cube"></i>
            </div>
            <a href="{{ route('kategori.index') }}" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col --><!-- visit "codeastro" for more projects! -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>{{ $produk }}</h3>

                <p>Total Product</p>
            </div>
            <div class="icon">
                <i class="fa fa-cubes"></i>
            </div>
            <a href="{{ route('produk.index') }}" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $member }}</h3>

                <p>Total Worker</p>
            </div>
            <div class="icon">
                <i class="fa fa-id-card"></i>
            </div>
            <a href="{{ route('member.index') }}" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-olive">
            <div class="inner">
                <h3>{{ $supplier }}</h3>

                <p>Total Supplier</p>
            </div>
            <div class="icon">
                <i class="fa fa-truck"></i>
            </div>
            <a href="{{ route('supplier.index') }}" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->

<div class="row">
<div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $penjualan }}</h3>

                <p>Sales</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
            <a href="{{ route('penjualan.index') }}" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->

    <div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>{{ $pengeluaran }}</h3>

                <p>Total Expenses</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
            <a href="{{ route('pengeluaran.index') }}" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <!-- visit "codeastro" for more projects! -->
    <div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $pembelian }}</h3>

                <p>Total Purchase</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
            <a href="{{ route('pembelian.index') }}" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->

    <!-- visit "codeastro" for more projects! -->
</div>

<div class="row">
    <div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-primary">
            <div class="inner">
                @php
                    $totalCashPaid = $total_invoice
                    ->where('payment_received', 'cash')
                    ->where('payment_status', 'paid')
                    ->sum(function ($invoice) {
                        return (float) $invoice->grand_total;
                    });
                @endphp
                <h3>{{ $totalCashPaid }}</h3>

                <p>Total Cash Amount Reveiced</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
            <a href="#" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->

    <div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-purple">
            <div class="inner">
                @php
                    $totalAcountPaid = $total_invoice
                    ->whereNotIn('payment_received', ['cash', 'other'])
                    ->where('payment_status', 'paid')
                    ->sum('grand_total');
                @endphp
                <h3>{{ $totalAcountPaid }}</h3>

                <p>Total Account Payment </p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
            <a href="{{ route('pengeluaran.index') }}" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <!-- visit "codeastro" for more projects! -->
    <div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                @php
                    $pendingCashPaid = $total_invoice
                    ->where('payment_status', 'partial')
                    ->sum(function ($invoice) {
                        return (float) $invoice->remaining_amount;
                    });
                @endphp
                <h3>{{ $pendingCashPaid }}</h3>

                <p>Total Pending Payment</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
            <a href="{{ route('pembelian.index') }}" class="small-box-footer">View <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->

    <!-- visit "codeastro" for more projects! -->
</div>
<!-- Main row -->
<div class="row">
    <div class="col-lg-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Income Chart {{ tanggal_indonesia($tanggal_awal, false) }} - {{ tanggal_indonesia($tanggal_akhir, false) }}</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="chart">
                            <!-- Sales Chart Canvas -->
                            <canvas id="salesChart" style="height: 280px;"></canvas>
                        </div>
                        <!-- /.chart-responsive -->
                    </div>
                </div>
                <!-- /.row -->
            </div>
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
    <div class="col-lg-6">
    <div class="box box-primary" style="
        min-height: 350px !important;
        overflow-y: auto;">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-line-chart"></i> Top Selling Products
            </h3>
        </div>

        <div class="box-body">
            <ul class="products-list product-list-in-box">
                @foreach ($top_selling_products as $index => $product)
                    <li class="item">
                        <div class="product-info">
                            <span class="product-title">
                                {{-- Rank Icons --}}
                                @if ($index == 0) 🥇
                                @elseif ($index == 1) 🥈
                                @elseif ($index == 2) 🥉
                                @else #{{ $index + 1 }}
                                @endif

                                {{ $product->item_name }}

                                <span class="label label-success pull-right">
                                    {{ $product->total_quantity_sold }} sold
                                </span>
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
    <!-- /.col -->
</div>
<!-- /.row (main row) -->

<!-- Main row -->
<div class="row">
    <div class="col-lg-6">  </div>
</div>
@endsection
@push('style')
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

@endpush

@push('scripts')
<!-- ChartJS -->
<script src="{{ asset('AdminLTE-2/bower_components/chart.js/Chart.js') }}"></script>
<script>
$(function() {
    // Get context with jQuery - using jQuery's .get() method.
    var salesChartCanvas = $('#salesChart').get(0).getContext('2d');
    // This will get the first returned node in the jQuery collection.
    var salesChart = new Chart(salesChartCanvas);

    var salesChartData = {
        labels: {{ json_encode($data_tanggal) }},
        datasets: [
            {
                label: 'Pendapatan',
                fillColor           : 'rgba(60,141,188,0.9)',
                strokeColor         : 'rgba(60,141,188,0.8)',
                pointColor          : '#3b8bba',
                pointStrokeColor    : 'rgba(60,141,188,1)',
                pointHighlightFill  : '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                data: {{ json_encode($data_pendapatan) }}
            }
        ]
    };

    var salesChartOptions = {
        pointDot : false,
        responsive : true
    };

    salesChart.Line(salesChartData, salesChartOptions);
});
</script>
@endpush