@extends('layouts.master')

@section('title')
    Project List
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Project List</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm('{{ route('project.store') }}')" 
                        class="btn btn-success btn-flat">
                    <i class="fa fa-plus-circle"></i> Add New Project
                </button>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered table-hover" id="project-table">
                    <thead>
                        <tr>
                            <th width="3%">#</th>
                            <th>Code</th>
                            <th>Project Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th width="12%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>

@includeIf('project.form')
@endsection

@push('scripts')
<script>
    let table;

    $(function () {
        table = $('#project-table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('project.data') }}',
            },
            columns: [
                { data: 'DT_RowIndex', searchable: false, sortable: false },
                { data: 'project_code' },
                { data: 'project_name' },
                { data: 'project_phone_formatted' },
                { data: 'project_address_short' },
                { data: 'project_price_formatted' },
                { data: 'project_duration' },
                { data: 'project_status_badge', searchable: false },
                { data: 'created_at_formatted' },
                { data: 'aksi', searchable: false, sortable: false },
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (!e.preventDefault()) {
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
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
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Add Project');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=project_name]').focus();
    }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Project');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');

        $.get(url)
            .done((response) => {
                $('#modal-form [name=project_name]').val(response.project_name);
                $('#modal-form [name=project_phone]').val(response.project_phone);
                $('#modal-form [name=project_address]').val(response.project_address);
                $('#modal-form [name=project_price]').val(response.project_price);
                $('#modal-form [name=project_duration]').val(response.project_duration);
                $('#modal-form [name=project_details]').val(response.project_details);
                $('#modal-form [name=project_status]').val(response.project_status);
            })
            .fail(() => {
                alert('Unable to display data');
            });
    }

    function deleteData(url) {
        if (confirm('Are you sure you want to delete this project?')) {
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
</script>
@endpush
