@extends('layouts.master')

@section('title')
    Return Transaction List
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Return Transaction List</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">

            <div class="box-header with-border">
                <button onclick="addForm('{{ route('transaction-return.store') }}')"
                        class="btn btn-success btn-flat">
                    <i class="fa fa-plus-circle"></i> Return Transaction
                </button>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered table-hover" id="return-table">
                    <thead>
                        <tr>
                            <th width="3%">#</th>
                            <th>Return No</th>
                            <th>Invoice Ref</th>
                            <th>Invoice Code</th>
                            <th>Return Type</th>
                            <th>Return Amount</th>
                            <th>Return Amount In</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>

@includeIf('transaction.form')
@endsection


@push('scripts')
<script>
    let table;

    $(function () {
        table = $('#return-table').DataTable({
            processing: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('transaction-return.data') }}',
            },
            columns: [
                { data: 'no', searchable: false },
                { data: 'return_no' },
                { data: 'invoice_reference' },
                { data: 'invoice_code' },
                { data: 'return_type' },
                { data: 'return_amount' },
                { data: 'return_amount_in' },
                { data: 'created_at' },
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            e.preventDefault(); // prevent default behavior

            const $form = $(this).find('form');
            const url   = $form.attr('action');

            $.post(url, $form.serialize())
                .done((res) => {
                    // close modal
                    $('#modal-form').modal('hide');

                    // reload DataTable
                    table.ajax.reload(null, false);

                    // optional: show success message
                    if (res.message) {
                        alert(res.message); // you can replace with toastr/notification
                    }
                })
                .fail((xhr) => {
                    // parse Laravel validation errors
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let msg = '';
                        for (let key in errors) {
                            msg += `${errors[key].join(', ')}\n`;
                        }
                        alert(msg);
                    } else {
                        alert('Unable to save data.');
                    }
                });
        });

    });

    function initInvoiceReturn() {

        // ---------- helpers ----------
        function toNumber(v) {
            if (!v) return 0;
            return parseFloat(v.toString().replace(/,/g, '')) || 0;
        }

        function formatMoney(v) {
            return toNumber(v).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // ---------- modal ----------
        window.addForm = function (url) {
            const $modal = $('#modal-form');
            const $form  = $modal.find('form');

            $modal.modal('show');
            $modal.find('.modal-title').text('Invoice Transaction Return (Total Amount After Discount)');

            $form[0].reset();
            $form.attr('action', url);
            $form.find('[name=_method]').val('post');

            $('#invoice-items-wrapper').hide();
            $('#invoice-items-table tbody').html('');
            $('[name=return_amount]').val('');
        };

        // ---------- load invoice ----------
        $('#invoice_id').on('change', function () {
            const invoiceId = $(this).val();
            if (!invoiceId) return;

            $.get(`/partial-transaction-invoice/${invoiceId}`, function (res) {

                const subTotal   = toNumber(res.sub_total);
                const grandTotal = toNumber(res.grand_total);
                const ratio      = subTotal > 0 ? grandTotal / subTotal : 1;

                $('[name=invoice_grand_total]')
                    .val(formatMoney(grandTotal))
                    .data({
                        sub_total: subTotal,
                        grand_total: grandTotal,
                        ratio: ratio
                    });

                let rows = '';
                res.items.forEach(item => {
                    rows += `
                        <tr>
                            <td>${item.product_name ?? item.item_name}</td>
                            <td>${item.quantity}</td>
                            <td>
                                <input type="number"
                                    name="items[${item.id}][return_qty]"
                                    class="form-control input-sm return-qty"
                                    min="0"
                                    max="${item.quantity}"
                                    value="0"
                                    data-price="${toNumber(item.price)}">
                            </td>
                        </tr>
                    `;
                });

                $('#invoice-items-table tbody').html(rows);
                $('#invoice-items-wrapper').show();
            });
        });

        // ---------- return type ----------
        $('#return_type').on('change', function () {
            const type = $(this).val();
            const grandTotal = $('[name=invoice_grand_total]').data('grand_total') || 0;

            if (type === 'full') {

                $('.return-qty').each(function () {
                    $(this).val($(this).attr('max')).prop('readonly', true);
                });

                $('[name=return_amount]')
                    .val(formatMoney(grandTotal))
                    .prop('readonly', true);

            } else if (type === 'partial') {

                $('.return-qty').each(function () {
                    $(this).val(0).prop('readonly', false);
                });

                $('[name=return_amount]')
                    .val('0.00')
                    .prop('readonly', true);
            }
        });

        // ---------- partial calculation (CORRECT) ----------
        $(document).on('input', '.return-qty', function () {
            if ($('#return_type').val() !== 'partial') return;

            let returnSubTotal = 0;

            $('.return-qty').each(function () {
                const qty   = toNumber($(this).val());
                const price = toNumber($(this).data('price'));
                returnSubTotal += qty * price;
            });

            const ratio = $('[name=invoice_grand_total]').data('ratio') || 1;

            const finalReturn = returnSubTotal * ratio;

            $('[name=return_amount]').val(formatMoney(finalReturn));
        });
    }

    // init once
    $(document).ready(function () {
        initInvoiceReturn();
    });
</script>



@endpush

