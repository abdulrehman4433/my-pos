@extends('layouts.master')

@section('title')
    Invoice List
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
                            <th>Discount</th>
                            <th>Grand Total</th>
                            <th>Paid</th>
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
@endsection

@push('scripts')
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

        // Generate & set the invoice code
        const code = generateInvoiceCode();
        $form.find('#invoice_code').val(formatInvoiceCode(code, 'INV-'));

        // Focus whatever field you prefer next:
        $form.find('[name=invoice_reference]').focus();
    }


    let selectedProducts = [];

    function referenceChanged(select) {
        const value = select.value;
        const referenceGroup = $('#reference_id_group');
        const referenceContainer = $('#reference_id_container');
        const subTotalInput = $('input[name="sub_total"]');

        subTotalInput.val(0);
        referenceContainer.empty();

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
                        selectEl.append(`
                            <option value="${item.product_id}"
                                data-name="${item.product_name}"
                                data-price="${item.selling_price}">
                                ${item.product_name} (PKR ${item.selling_price})
                            </option>
                        `);
                    });

                    referenceContainer.append(selectEl);
                    referenceGroup.show();

                    selectEl.on('change', function () {
                        const opt = $(this).find(':selected');
                        if (!opt.val()) return;

                        const product = {
                            id: opt.val(),
                            name: opt.data('name'),
                            price: parseFloat(opt.data('price')),
                            qty: parseInt($('#temp_quantity').val()) || 1
                        };

                        addProductRow(product);
                        $(this).val('');
                    });
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

        const rowTotal = product.price * product.qty;

        const row = `
            <tr data-id="${product.id}">
                <td>
                    ${product.name}
                    <input type="hidden" name="products[${product.id}][id]" value="${product.id}">
                </td>
                <td>
                    ${product.price}
                    <input type="hidden" name="products[${product.id}][price]" value="${product.price}">
                </td>
                <td>
                    <input type="number" min="1" value="${product.qty}"
                        name="products[${product.id}][qty]"
                        class="form-control qty-input">
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
    }

    $(document).on('input', '.qty-input', function () {
        const row = $(this).closest('tr');
        const price = parseFloat(row.find('input[name$="[price]"]').val());
        const qty = parseInt($(this).val()) || 1;
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

</script>
@endpush
