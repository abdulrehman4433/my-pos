@extends('layouts.master')

@section('title')
    Maintenance List
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Maintenance List</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm('{{ route('maintenance.store') }}')" class="btn btn-success btn-flat">
                    <i class="fa fa-plus-circle"></i> Add New Maintenance
                </button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered table-hover">
                    <thead>
                        <th width="5%">#</th>
                        <th>Name</th>
                        <th>Telephone</th>
                        <th>Address</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('maintenance.form') {{-- create a modal form for maintenance --}}
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
                url: '{{ route('maintenance.data') }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'maintenance_name'},
                {data: 'maintenance_phone'},
                {data: 'maintenance_address'},
                {data: 'maintenance_price'},
                {data: 'maintenance_duration'},
                {data: 'status', searchable: false, sortable: false},
                {data: 'actions', searchable: false, sortable: false},
            ]
        });

        // Form submit
        $('#modal-form').validator().on('submit', function (e) {
            if (!e.preventDefault()) {
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done((response) => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Unable to save data');
                        return;
                    });
            }
        });
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Add Maintenance');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=maintenance_name]').focus();
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Maintenance');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=maintenance_name]').focus();

        $.get(url)
            .done((response) => {
                $('#modal-form [name=maintenance_name]').val(response.maintenance_name);
                $('#modal-form [name=maintenance_phone]').val(response.maintenance_phone);
                $('#modal-form [name=maintenance_address]').val(response.maintenance_address);
                $('#modal-form [name=maintenance_price]').val(response.maintenance_price);
                $('#modal-form [name=maintenance_duration]').val(response.maintenance_duration);
                $('#modal-form [name=maintenance_details]').val(response.maintenance_details);
                $('#modal-form [name=is_active]').prop('checked', response.is_active);
            })
            .fail((errors) => {
                alert('Unable to display data');
                return;
            });
    }

    function deleteData(url) {
        if (confirm('Are you sure you want to delete selected data?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload();
                })
                .fail((errors) => {
                    alert('Unable to delete data');
                    return;
                });
        }
    }
</script>
@endpush
