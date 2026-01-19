@extends('layouts.master')

@section('title')
    Rental List
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Rental List</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm('{{ route('rental.store') }}')" class="btn btn-success btn-flat">
                    <i class="fa fa-plus-circle"></i> Add New Rental
                </button>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th>Product</th>
                            <th>Person</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('rental.form')
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
                url: '{{ route('rental.data') }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'rental_product'},
                {data: 'rental_person'},
                {data: 'phone_formatted'},
                {data: 'address_short'},
                {data: 'price_formatted'},
                {data: 'status', searchable: false, sortable: false},
                {data: 'actions', searchable: false, sortable: false},
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

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Add Rental');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=rental_product]').focus();
        const get_url = '{{ route('invoice.product') }}';
        $.get(get_url)
        .done((response) => {
            let $select = $('#rental_product');
            $select.empty(); // clear old options

            $select.append('<option value="">-- Select Product --</option>');

            response.forEach(item => {
                $select.append(`
                    <option 
                        value="${item.product_id}"
                        data-price="${item.selling_price}"
                        data-stock="${item.stock}">
                        ${item.product_name}
                    </option>
                `);
            });
        })
        .fail(() => {
            alert('Unable to display data');
        });
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Rental');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');

        const product_url = '{{ route('invoice.product') }}';

        // 1️⃣ Get rental data
        $.get(url)
            .done((response) => {

                // 2️⃣ Load product list
                $.get(product_url)
                    .done((products) => {

                        let $select = $('#rental_product');
                        $select.empty();
                        $select.append('<option value="">-- Select Product --</option>');

                        products.forEach(item => {
                            $select.append(`
                                <option 
                                    value="${item.product_id}"
                                    data-price="${item.selling_price}"
                                    data-stock="${item.stock}">
                                    ${item.product_name}
                                </option>
                            `);
                        });

                        // 3️⃣ Select saved product
                        $select.val(response.rental_product_id);
                    });

                // 4️⃣ Fill other fields
                $('#modal-form [name=rental_person]').val(response.rental_person);
                $('#modal-form [name=rental_person_phone]').val(response.rental_person_phone);
                $('#modal-form [name=rental_person_address]').val(response.rental_person_address);
                $('#modal-form [name=rental_price]').val(response.rental_price);
                $('#modal-form [name=rental_duration]').val(response.rental_duration);
                $('#modal-form [name=rental_status]').val(response.rental_status);
            })
            .fail(() => {
                alert('Unable to display data');
            });
    }

    function deleteData(url) {
        if (confirm('Are you sure you want to delete this rental?')) {
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
