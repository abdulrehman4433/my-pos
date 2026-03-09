@extends('layouts.master')

@section('title')
    List of All Invoice 
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Invoice List</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">

            <div class="box-header with-border">
                <button onclick="addForm('{{ route('invoice.store') }}')"
                        class="btn btn-success btn-flat">
                    <i class="fa fa-plus-circle"></i> Add New Invoice
                </button>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered table-hover" id="invoice-table">
                    <thead>
                        <tr>
                            <th width="3%">#</th>
                            <th>Invoice Code</th>
                            <th>Invoice Ref</th>
                            <th>Sub Total</th>
                            <th>Discount %</th>
                            <th>Grand Total</th>
                            <th>Paid</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th width="12%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>

@includeIf('invoice.form')
@includeIf('invoice.discount-calculator')
@endsection

@push('scripts')

<style>
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
    /* Make select2 option text white on hover */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        color: #fff !important;
    }
    .input-group .btn{
        padding: 6px 10px;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
let table;

$(function () {
    table = $('#invoice-table').DataTable({
        processing: true,
        autoWidth: false,
        ajax: {
            url: '{{ route('invoice.data') }}',
        },
        columns: [
            { data: 'no', searchable: false },
            { data: 'invoice_code' },
            { data: 'invoice_reference' },
            { data: 'sub_total' },
            { data: 'discount_amount' },
            { data: 'grand_total' },
            { data: 'payment_received' },
            { data: 'remaining_amount' },
            { data: 'payment_status', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false },
        ]
    });

    $('#modal-form').validator().on('submit', function (e) {
        if (!e.preventDefault()) {
            $.post($('#modal-form form').attr('action'),
                $('#modal-form form').serialize()
            )
            .done(() => {
                $('#modal-form').modal('hide');
                table.ajax.reload(null, false);
            })
            .fail(() => {
                alert('Unable to save data');
            });
        }
    });
});

// Generates a 6-digit code as a string, preserving leading zeros.
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

function addForm(url) {
    const $modal = $('#modal-form');

    $modal.modal('show');
    $modal.find('.modal-title').text('Add Invoice');

    const $form = $modal.find('form');
    $form[0].reset();
    $form.attr('action', url);
    $form.find('[name=_method]').val('post');
    $('#products_table tbody').empty();

    // Reset subtotal
    $('input[name="sub_total"]').val('0');
    $('#table-total').text('0.00');

    // Generate & set the invoice code
    const code = generateInvoiceCode();
    $form.find('#invoice_code').val(formatInvoiceCode(code, 'INV-'));

    // Focus whatever field you prefer next:
    $form.find('[name=invoice_reference]').focus();
}

let selectedProducts = [];
let productStockMap = {}; // Store product stock data

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
                // Load Select2 CSS/JS if not already loaded
                if (typeof $.fn.select2 === 'undefined') {
                    // CDN fallback
                    $('head').append('<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />');
                    $.getScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', function() {
                        createProductSelect(res);
                    });
                } else {
                    createProductSelect(res);
                }

                function createProductSelect(res) {
                    let selectEl = $('<select>', {
                        class: 'form-control',
                        id: 'product_select',
                        style: 'width:100%'
                    });

                    selectEl.append('<option value="">Select Product</option>');

                    res.forEach(item => {
                        const availableStock = item.stock ? item.stock.stock : 0;
                        // Store stock data for validation
                        productStockMap[item.product_id] = {
                            available: availableStock,
                            minimum: item.stock ? item.stock.minimum_stock : 0
                        };
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

                    // Initialize Select2 for search by name
                    selectEl.select2({
                        dropdownParent: referenceGroup,
                        placeholder: 'Select Product',
                        allowClear: true,
                        width: 'resolve'
                    });

                    selectEl.on('select2:select', function (e) {
                        const opt = $(this).find(':selected');
                        if (!opt.val()) return;

                        const productId = opt.val();
                        const availableStock = parseInt(opt.data('stock')) || 0;
                        
                        // Always default to 1 when adding new product
                        const requestedQty = 1;

                        // Validate stock availability
                        if (requestedQty > availableStock) {
                            alert(`Insufficient stock!\n\nProduct: ${opt.data('name')}\nAvailable: ${availableStock}\nRequested: ${requestedQty}`);
                            $(this).val('').trigger('change');
                            return;
                        }

                        const product = {
                            id: productId,
                            code: opt.data('code') || '',
                            name: opt.data('name'),
                            price: parseFloat(opt.data('price')),
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
        const currentQty = parseInt(qtyInput.val()) || 0;
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
    const rowTotal = (product.price * product.qty).toFixed(2);

    const row = `
        <tr data-id="${product.id}" data-stock="${product.availableStock}">
            <td>${product.code || 'N/A'}</td>
            <td>${product.name}
                <input type="hidden" name="products[${product.id}][id]" value="${product.id}">
            </td>
            <td>${product.brand || 'N/A'}</td>
            <td>${product.variant || 'N/A'}</td>
            <td>${product.unit || 'N/A'}</td>
            <td class="product-price" data-price="${product.price}">${product.price.toFixed(2)}
                <input type="hidden" name="products[${product.id}][price]" value="${product.price}">
            </td>
            <td>
                <input type="number" min="1" max="${product.availableStock}" value="${product.qty}"
                    name="products[${product.id}][qty]"
                    class="form-control qty-input"
                    data-product-id="${product.id}"
                    data-price="${product.price}"
                    data-max-stock="${product.availableStock}">
            </td>
            <td class="line-total">${rowTotal}</td>
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
    
    $('#products_table tbody tr').each(function() {
        const lineTotal = parseFloat($(this).find('.line-total').text()) || 0;
        total += lineTotal;
    });
    
    // Format to 2 decimal places
    const formattedTotal = total.toFixed(2);
    $('input[name="sub_total"]').val(formattedTotal);
    $('#table-total').text(formattedTotal);
    
    // Also update grand total if discount exists
    updateGrandTotal();
}

function updateGrandTotal() {
    const subtotal = parseFloat($('input[name="sub_total"]').val()) || 0;
    const discount = parseFloat($('#discount_amount').val()) || 0;
    const tax = parseFloat($('input[name="tax_amount"]').val()) || 0;
    
    const grandTotal = (subtotal - discount + tax).toFixed(2);
    $('input[name="grand_total"]').val(grandTotal);
}

$(document).on('input', '.qty-input', function () {
    const row = $(this).closest('tr');
    const maxStock = parseInt($(this).data('max-stock')) || 0;
    const price = parseFloat($(this).data('price')) || 0;
    
    let qty = parseInt($(this).val());
    
    // Validate quantity
    if (isNaN(qty) || qty < 1) {
        qty = 1;
        $(this).val(1);
    }
    
    // Enforce max stock limit
    if (qty > maxStock) {
        qty = maxStock;
        $(this).val(maxStock);
        alert(`Maximum available stock is ${maxStock}`);
    }

    // Calculate and update line total
    const lineTotal = (price * qty).toFixed(2);
    row.find('.line-total').text(lineTotal);
    
    // Update subtotal
    updateSubTotal();
});

$(document).on('change', '.qty-input', function () {
    // Trigger input event to ensure calculation
    $(this).trigger('input');
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
            
            // Load products if they exist in the response
            if (res.products && res.products.length > 0) {
                $('#products_table tbody').empty();
                res.products.forEach(product => {
                    const productData = {
                        id: product.id,
                        code: product.code,
                        name: product.name,
                        price: parseFloat(product.price),
                        qty: parseInt(product.qty),
                        availableStock: product.stock || 0,
                        unit: product.unit || '',
                        brand: product.brand || '',
                        variant: product.variant || ''
                    };
                    
                    // Directly add row without validation for edit mode
                    const rowTotal = (productData.price * productData.qty).toFixed(2);
                    const row = `
                        <tr data-id="${productData.id}" data-stock="${productData.availableStock}">
                            <td>${productData.code || 'N/A'}</td>
                            <td>${productData.name}
                                <input type="hidden" name="products[${productData.id}][id]" value="${productData.id}">
                            </td>
                            <td>${productData.brand || 'N/A'}</td>
                            <td>${productData.variant || 'N/A'}</td>
                            <td>${productData.unit || 'N/A'}</td>
                            <td class="product-price" data-price="${productData.price}">${productData.price.toFixed(2)}
                                <input type="hidden" name="products[${productData.id}][price]" value="${productData.price}">
                            </td>
                            <td>
                                <input type="number" min="1" max="${productData.availableStock}" value="${productData.qty}"
                                    name="products[${productData.id}][qty]"
                                    class="form-control qty-input"
                                    data-product-id="${productData.id}"
                                    data-price="${productData.price}"
                                    data-max-stock="${productData.availableStock}">
                            </td>
                            <td class="line-total">${rowTotal}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-xs remove-row">×</button>
                            </td>
                        </tr>
                    `;
                    $('#products_table tbody').append(row);
                });
                updateSubTotal();
            }
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

    // reset
    $container.empty();
    $group.hide();

    // NEW CUSTOMER (no server call)
    if (value === 'new_customer') {
        
        $group.find('label').text('Customer Details');
        $group.show();

        const html = `
                <input type="text"
                       class="form-control"
                       name="new_customer_name"
                       style="margin-bottom:10px;"
                       placeholder="Enter customer name">
                <input type="text"
                       class="form-control"
                       name="new_customer_mobile"
                       placeholder="Enter mobile number">
        `;

        $container.append(html);

        return;
    }

    // EXISTING CUSTOMER
    if (value === 'customer') {
        $group.find('label').text('Select Customer');
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

            error: function () {

                $container.append('<span class="text-danger">Failed to load customers</span>');

            }

        });

    }

}

$(document).on('change', '#resource_id', function () {
    const discount = $(this).find(':selected').data('discount');
    $('#discount_amount').val(discount ?? 0);
    updateGrandTotal();
});

$(document).on('input', '#discount_amount, [name="tax_amount"]', function() {
    updateGrandTotal();
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
    
    // Initialize any existing quantity inputs
    $(document).on('focus', '.qty-input', function() {
        $(this).trigger('input');
    });
});

$(document).ready(function(){

    function calculateDiscount(){
        let amount = parseFloat($('#calc_amount').val());
        let discount = parseFloat($('#calc_discount').val());

        if(!isNaN(amount) && amount > 0 && !isNaN(discount)){
            let percent = (discount / amount) * 100;
            $('#calc_percentage').val(percent.toFixed(2));
        }
    }

    $('#calc_amount, #calc_discount').on('keyup change', function(){
        calculateDiscount();
    });

    $('#apply_discount').click(function(){

        let percentage = $('#calc_percentage').val();

        if(percentage){
            $('#discount_amount').val(percentage);
        }

        $('#discountCalculatorModal').modal('hide');
    });

});
</script>


@endpush
