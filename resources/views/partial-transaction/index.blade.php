@extends('layouts.master')

@section('title')
    Clear Invoice Remaing Amount
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Clear Invoice</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">

            <div class="box-header with-border">
                <h3 class="box-title">Partial OR Unpaid Invoice List</h3>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped table-hover" id="partial-table">
                    <thead>
                        <tr>
                            <th width="3%">#</th>
                            <th>Invoice Ref</th>
                            <th>Invoice Code</th>
                            <th>Sub Total</th>
                            <th>Discount %</th>
                            <th>Grand Total</th>
                            <th>Received</th>
                            <th>Remaining</th>
                            <th>Payment Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($invoices as $row)
                            <tr>
                                <td>{{ $row['no'] }}</td>
                                <td>{{ $row['invoice_reference'] }}</td>
                                <td>{{ $row['invoice_code'] }}</td>
                                <td>{{ $row['sub_total'] }}</td>
                                <td>{{ $row['discount_amount'] }} %</td>
                                <td>{{ $row['grand_total'] }}</td>
                                <td class="text-success">
                                    {{ $row['received_amount'] }}
                                </td>
                                <td>
                                    {{ $row['remaining_amount'] }}
                                </td>
                                <td>{!! $row['payment_status'] !!}</td>
                                <td>{{ $row['created_at'] }}</td>
                                <td>
                                    <button onclick="showDetail('{{$row['id']}}', '{{$row['invoice_code']}}')"
                                            class="btn btn-info btn-sm btn-flat">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">
                                    No partial return transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>
</div>
@includeIf('partial-transaction.form')
@endsection
@push('scripts')
<script>
    $(function () {
        $('#partial-table').DataTable({
            processing: true,
            autoWidth: false,
            order: [[9, 'desc']], // sort by Date column
            columnDefs: [
                { targets: [0, 10], orderable: false }, // # and Action
                { targets: [10], searchable: false },  // Action not searchable
            ]
        });
    });

    function showDetail(id, invoiceCode) {
        const $modal = $('#modal-form');

        $modal.modal('show');
        $modal.find('.modal-title').text('Clear Remaining Invoice Amount');
        const $form = $modal.find('form');
        $form[0].reset();
        $form.find('[name=invoice_code]').val(invoiceCode);
        $form.find('[name=invoice_id]').val(id);
        const url = '{{ route("partial-transaction-invoice", ":id") }}'.replace(':id', id);

        $.get(url)
            .done((res) => {
                $('#modal-form [name=invoice_reference]').val(res.invoice_reference);
                $('#modal-form [name=received_amount]').val(res.received_amount);
                $('#modal-form [name=remaining_amount]').val(res.remaining_amount);
            })
            .fail(() => {
                alert('Unable to display data');
            });
    }
</script>
@endpush
