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
                            <th>Payment Status</th>
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
                { data: 'return_type', orderable: false, searchable: false },
                { data: 'return_amount' },
                { data: 'payment_status', orderable: false, searchable: false },
                { data: 'created_at' },
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

    function addForm(url) {
        const $modal = $('#modal-form');

        $modal.modal('show');
        $modal.find('.modal-title').text('Add Invoice Return Transaction');

        const $form = $modal.find('form');
        $form[0].reset();
        $form.attr('action', url);
        $form.find('[name=_method]').val('post');
    }

    $(document).ready(function() {
        $(document).on('change', '#invoice_id', function() {
            const invoiceId = $(this).val();
            if (invoiceId) {
                $.ajax({
                    url: `/partial-transaction-invoice/${invoiceId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('input[name="invoice_grand_total"]').attr('readonly', true);
                        $('input[name="invoice_grand_total"]').val(data.grand_total);
                    },
                    error: function() {
                        alert('Error retrieving invoice data.');
                    }
                });
            }
        });

        $(document).on('change', '#return_type', function() {
            const returnType = $(this).val();
            const grandTotal = $('input[name="invoice_grand_total"]').val();
            if (returnType === 'full') {
                $('input[name="return_amount"]').val(grandTotal).attr('readonly', true);
            } else {
                $('input[name="return_amount"]').val('').attr('readonly', false);
            }
        });
    });

</script>
@endpush

