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
        <div class="small-box bg-green" onclick="addForm('{{ route('invoice.store') }}')">
            <div class="inner">
                <h3>{{ $penjualan }}</h3>
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
                <h3>{{ $kategori }}</h3>

                <p class="p-20">Total Categories</p>
            </div>
            <div class="icon">
                <i class="fa fa-cube"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col --><!-- visit "codeastro" for more projects! -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('produk.index') }}">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>{{ $produk }}</h3>

                <p class="p-20">Total Product</p>
            </div>
            <div class="icon">
                <i class="fa fa-cubes"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('member.index') }}">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $member }}</h3>

                <p class="p-20">Total Worker</p>
            </div>
            <div class="icon">
                <i class="fa fa-id-card"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->

<div class="row">
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('supplier.index') }}">
        <div class="small-box bg-olive">
            <div class="inner">
                <h3>{{ $supplier }}</h3>

                <p class="p-20">Total Supplier</p>
            </div>
            <div class="icon">
                <i class="fa fa-truck"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('penjualan.index') }}">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>{{ $penjualan }}</h3>

                <p class="p-20">Sales</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col -->

    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('pengeluaran.index') }}">
        <div class="small-box bg-red">
            <div class="inner">
                <h3>{{ $pengeluaran }}</h3>

                <p class="p-20">Total Expenses</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col -->
    <!-- visit "codeastro" for more projects! -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('pembelian.index') }}">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $pembelian }}</h3>

                <p class="p-20">Total Purchase</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col -->

    <!-- visit "codeastro" for more projects! -->
</div>

<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="#">
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

                <p class="p-20">Total Cash Amount Reveiced</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col -->

    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('penjualan.index') }}">
        <div class="small-box bg-olive">
            <div class="inner">
                @php
                    $totalAcountPaid = $total_invoice
                    ->whereNotIn('payment_received', ['cash', 'other'])
                    ->where('payment_status', 'paid')
                    ->sum('grand_total');
                @endphp
                <h3>{{ $totalAcountPaid }}</h3>

                <p class="p-20">Total Account Payment </p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col -->
    <!-- visit "codeastro" for more projects! -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="{{ route('partial-transaction-return.data') }}">
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

                <p class="p-20">Total Pending Payment</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
    </div>
    <!-- ./col -->
    
    <!-- visit "codeastro" for more projects! -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <a href="#">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>{{ $totalProfit }}</h3>

                <p class="p-20">Total Profit</p>
            </div>
            <div class="icon">
                <i class="fa fa-dollar"></i>
            </div>
        </div>
        </a>
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
    <div class="col-lg-6"> 
    <div class="box box-primary" style="
        min-height: 350px !important;
        overflow-y: auto;">
        <div class="box-header with-border">
            <h3 class="box-title">
                 Products go out of stock soon
            </h3>
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
                <tbody>
                    @foreach ($lowStockProducts as $product)
                    <tr>
                        <td>{{ $product->product_name }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ $product->minimum_stock }}</td>
                    </tr>
                    @endforeach
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
        /* Fix select option text color for product_select */
        #product_select, #product_select option {
            color: #222 !important;
            background-color: #fff !important;
        }
    </style>
@endpush

@push('scripts')
<!-- ChartJS -->
<script src="{{ asset('AdminLTE-2/bower_components/chart.js/Chart.js') }}"></script>
<script>
// filepath: c:\xampp\htdocs\my-pos\resources\views\admin\dashboard.blade.php

$(function() {
    // Get context with jQuery - using jQuery's .get() method.
    var salesChartCanvas = $('#salesChart').get(0).getContext('2d');
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

// Move addForm function outside of DOMContentLoaded
function addForm(url) {
    const $modal = $('#modal-form');

    $modal.modal('show');
    $modal.find('.modal-title').text('Add Invoice');

    const $form = $modal.find('form');
    $form[0].reset();
    $form.attr('action', url);
    $form.find('[name=_method]').val('post');
    $('#products_table tbody').empty();
    // Add or update hidden input to indicate dashboard source
    let $sourceInput = $form.find('input[name="from_dashboard"]');
    if ($sourceInput.length === 0) {
        $form.append('<input type="hidden" name="from_dashboard" value="1">');
    } else {
        $sourceInput.val('1');
    }

    // Generate & set the invoice code
    const code = generateInvoiceCode();
    $form.find('#invoice_code').val(formatInvoiceCode(code, 'INV-'));

    // Focus whatever field you prefer next:
    $form.find('[name=invoice_reference]').focus();
}

// Move generateInvoiceCode function outside of DOMContentLoaded
function generateInvoiceCode() {
    const array = new Uint32Array(1);
    window.crypto.getRandomValues(array);
    const num = array[0] % 1_000_000;
    return String(num).padStart(6, '0');
}

// Optional: if you want a prefix like INV-123456
function formatInvoiceCode(code, prefix = '') {
    return prefix ? `${prefix}${code}` : code;
}

// Move referenceChanged function outside of DOMContentLoaded
function referenceChanged(select) {
    const value = select.value;
    const referenceGroup = $('#reference_id_group');
    const referenceContainer = $('#reference_id_container');
    const subTotalInput = $('input[name="sub_total"]');

    subTotalInput.val(0);
    referenceContainer.empty();
    productStockMap = {}; // Reset stock map

    if (value === 'product') {
        $('#quantity_group').hide();
        $('#sub_total_group').hide();
        $('#products_table_group').show();
        subTotalInput.prop('readonly', true);

        $.get('{{ route('invoice.product') }}')
            .done(res => {
                let selectEl = $('<select>', {
                    class: 'form-control',
                    id: 'product_select'
                });

                selectEl.append('<option value="">Select Product</option>');

                res.forEach(item => {
                    const availableStock = item.stock ? item.stock.stock : 0;
                    // Store stock data for validation
                    productStockMap[item.product_id] = { availableStock };

                    selectEl.append(`
                        <option value="${item.product_id}"
                            data-name="${item.product_name}"
                            data-price="${item.selling_price}"
                            data-stock="${availableStock}"
                            data-code="${item.product_code}"
                            data-unit="${item.unit}"
                            data-brand="${item.brand}"
                            data-variant="${item.variant}">
                            ${item.product_name} (RS ${item.selling_price}) - Stock: ${availableStock}
                        </option>
                    `);
                });

                referenceContainer.append(selectEl);
                referenceGroup.show();

                selectEl.on('change', function () {
                    const opt = $(this).find(':selected');
                    if (!opt.val()) return;

                    const productId = opt.val();
                    const availableStock = parseInt(opt.data('stock')) || 0;
                    const requestedQty = parseInt($('#temp_quantity').val()) || 1;

                    // Validate stock availability
                    if (requestedQty > availableStock) {
                        alert(`Insufficient stock!\n\nProduct: ${opt.data('name')}\nAvailable: ${availableStock}\nRequested: ${requestedQty}`);
                        $(this).val('');
                        return;
                    }

                    const product = {
                        id: productId,
                        code: opt.data('code') || '',
                        name: opt.data('name'),
                        price: Number(opt.attr('data-price')) || 0,
                        qty: requestedQty,
                        availableStock: availableStock,
                        unit: opt.data('unit') || '',
                        brand: opt.data('brand') || '',
                        variant: opt.data('variant') || ''
                    };

                    addProductRow(product);
                    $(this).val('');
                });
            })
            .fail(() => {
                alert('Failed to load products');
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

function addProductRow(product) {
    // Check if product already exists in table
    const existingRow = $(`#products_table tbody tr[data-id="${product.id}"]`);
    
    if (existingRow.length > 0) {
        // Product exists, validate total quantity
        const qtyInput = existingRow.find('.qty-input');
        const currentQty = parseInt(qtyInput.val()) || 1;
        const newQty = currentQty + product.qty;

        // Validate new total doesn't exceed stock
        if (newQty > product.availableStock) {
            alert(`Cannot add more!\n\nProduct: ${product.name}\nAvailable Stock: ${product.availableStock}\nCurrent Qty: ${currentQty}\nRequested Add: ${product.qty}\nTotal Would Be: ${newQty}`);
            return;
        }

        qtyInput.val(newQty);
        qtyInput.trigger('input');
        return;
    }

    // Product doesn't exist, create new row
    const rowTotal = product.price * product.qty;

    const row = `
        <tr data-id="${product.id}" data-stock="${product.availableStock}">
            <td>${product.code || 'N/A'}</td>
            <td>${product.name}
                <input type="hidden" name="products[${product.id}][id]" value="${product.id}">
            </td>
            <td>${product.brand || 'N/A'}</td>
            <td>${product.variant || 'N/A'}</td>
            <td>${product.unit || 'N/A'}</td>
            <td>${product.price}
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
        total += parseFloat($(this).find('.line-total').text());
    });
    $('input[name="sub_total"]').val(total.toFixed(2));
        
    // Update table footer total
    $('#table-total').text(total.toFixed(2));
}

$(document).on('input', '.qty-input', function () {
    const row = $(this).closest('tr');
    const maxStock = parseInt($(this).data('max-stock')) || 0;
    let qty = parseInt($(this).val()) || 1;

    // Enforce max stock limit
    if (qty > maxStock) {
        qty = maxStock;
        $(this).val(qty);
        alert(`Maximum available stock is ${maxStock}`);
    }

    const price = parseFloat(row.find('input[name$="[price]"]').val());
    row.find('.line-total').text((price * qty).toFixed(2));
    updateSubTotal();
});

$(document).on('click', '.remove-row', function () {
    $(this).closest('tr').remove();
    updateSubTotal();
});

function editForm(url) {
    $('#modal-form').modal('show');
    $('#modal-form .modal-title').text('Edit Invoice');

    $('#modal-form form')[0].reset();
    $('#modal-form form').attr('action', url);
    $('#modal-form [name=_method]').val('put');

    $.get(url)
        .done((res) => {
            $('#modal-form [name=invoice_reference]').val(res.invoice_reference);
            $('#modal-form [name=reference_id]').val(res.reference_id);
            $('#modal-form [name=sub_total]').val(res.sub_total);
            $('#modal-form [name=tax_amount]').val(res.tax_amount);
            $('#modal-form [name=discount_amount]').val(res.discount_amount);
            $('#modal-form [name=payment_received]').val(res.payment_received);
            $('#modal-form [name=payment_status]').val(res.payment_status);
        })
        .fail(() => {
            alert('Unable to display data');
        });
}

function deleteData(url) {
    if (confirm('Are you sure you want to delete this invoice?')) {
        $.post(url, {
            '_token': $('[name=csrf-token]').attr('content'),
            '_method': 'delete'
        })
        .done(() => {
            table.ajax.reload(null, false);
        })
        .fail(() => {
            alert('Unable to delete data');
        });
    }
}

function viewForm(invoiceId) {
    // Generate URL dynamically
    const url = `/invoice/view/${invoiceId}`;
    window.open(url, '_blank'); // opens in new tab
}

function viewFormDownload(invoiceId) {
    // Open invoice in same tab or new tab with PDF print mode
    const url = `/invoice/view/${invoiceId}?pdf=1`;
    const w = window.open(url, '_blank'); // open in new tab

    // Wait for page to load, then call print
    w.onload = function() {
        w.print(); // triggers browser PDF dialog
    };
}

function invoiceResource(select) {
    const value = select.value;
    const $group = $('#resource_id_group');
    const $container = $('#resource_id_container');

    // Reset
    $container.empty();
    $group.hide();

    if (value !== 'customer') return;

    $group.show();

    $.ajax({
        url: "{{ route('invoice.customer') }}",
        type: "GET",
        dataType: "json",
        success: function (res) {
            // Create select element
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
                            text: item.name,
                            'data-name': item.name,
                            'data-discount': item.discount
                        })
                    );
                });
            } else {
                $select.append('<option value="">No customers found</option>');
            }

            $container.append($select);
        },
        error: function () {
            $container.append(
                '<span class="text-danger">Failed to load customers</span>'
            );
        }
    });
}

$(document).on('change', '#resource_id', function () {
    const discount = $(this).find(':selected').data('discount');

    $('#discount_amount').val(discount ?? 0);
});

$(document).ready(function() {
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
});
</script>
@endpush