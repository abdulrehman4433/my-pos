@extends('layouts.master')

@section('title')
    Customer List
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Customer List</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm('{{ route('customer.store') }}')" class="btn btn-success btn-flat">
                    <i class="fa fa-plus-circle"></i> Add New Customer
                </button>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Balance</th>
                            <th>Discount (%)</th>
                            <th>Status</th>
                            <th width="12%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('customer.form')
@endsection

@push('scripts')
<script>
    let table;

    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('customer.data') }}',
            },
            columns: [
                { data: 'DT_RowIndex', searchable: false, sortable: false },
                { data: 'customer_code' },
                { data: 'name' },
                { data: 'phone' },
                { data: 'address' },
                { data: 'current_balance' },
                { data: 'discount' },
                { data: 'is_active' },
                { data: 'aksi', searchable: false, sortable: false },
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (!e.preventDefault()) {
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done(() => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                    })
                    .fail(() => {
                        alert('Unable to save data');
                    });
            }
        });
    });
    function generateCustomerCode(length = 6) {
        return Math.floor(Math.random() * Math.pow(10, length));
    }


    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Add Customer');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=customer_code]').val(generateCustomerCode());
        $('#modal-form [name=customer_code]').focus();
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Customer');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');

        $.get(url)
            .done((response) => {
                $('#modal-form [name=customer_code]').val(response.customer_code);
                $('#modal-form [name=name]').val(response.name);
                $('#modal-form [name=phone]').val(response.phone);
                $('#modal-form [name=address]').val(response.address);
                $('#modal-form [name=current_balance]').val(response.current_balance);
                $('#modal-form [name=discount]').val(response.discount);
                $('#modal-form [name=is_active]').val(response.is_active);
                $('#modal-form [name=notes]').val(response.notes);
            })
            .fail(() => {
                alert('Unable to display data');
            });
    }

    function deleteData(url) {
        if (confirm('Are you sure you want to delete this customer?')) {
            $.post(url, {
                '_token': $('[name=csrf-token]').attr('content'),
                '_method': 'delete'
            })
            .done(() => {
                table.ajax.reload();
            })
            .fail(() => {
                alert('Unable to delete data');
            });
        }
    }
</script>
@endpush
