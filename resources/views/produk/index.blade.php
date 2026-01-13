@extends('layouts.master')

@section('title')
    Product List
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Product List</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <div class="btn-group">
                    <button onclick="addForm('{{ route('produk.store') }}')" class="btn btn-success  btn-flat"><i class="fa fa-plus-circle"></i> Add New Product</button>
                    <button onclick="deleteSelected('{{ route('produk.delete_selected') }}')" class="btn btn-danger  btn-flat"><i class="fa fa-trash"></i> Delete</button>
                    {{-- <button onclick="cetakBarcode('{{ route('produk.cetak_barcode') }}')" class="btn btn-warning  btn-flat"><i class="fa fa-barcode"></i> Print Barcode</button> --}}
                </div>
            </div>
            <div class="box-body table-responsive">
                <form action="" method="post" class="form-produk">
                    @csrf
                    <table class="table table-stiped table-bordered table-hover">
                        <thead>
                            <th width="5%">
                                <input type="checkbox" name="select_all" id="select_all">
                            </th>
                            <th width="5%">#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Purchase Price</th>
                            <th>Selling Price</th>
                            <th>Discount</th>
                            <th>Stock</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </thead>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

@includeIf('produk.form')
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
                url: '{{ route('produk.data') }}',
            },
            columns: [
                {data: 'select_all', searchable: false, sortable: false},
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'product_code'},           // Changed from 'kode_produk'
                {data: 'product_name'},           // Changed from 'nama_produk'
                {data: 'category_name'},          // Changed from 'nama_kategori'
                {data: 'brand'},                  // Changed from 'merk'
                {data: 'purchase_price'},         // Changed from 'harga_beli'
                {data: 'selling_price'},          // Changed from 'harga_jual'
                {data: 'discount'},               // Changed from 'diskon'
                {data: 'stock'},                  // Changed from 'stok'
                {data: 'action', searchable: false, sortable: false},  // Changed from 'aksi'
            ]
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (! e.preventDefault()) {
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

        $('[name=select_all]').on('click', function () {
            $(':checkbox').prop('checked', this.checked);
        });
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Add Product');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_produk]').focus();
    }

    function editForm(url) {
    $('#modal-form').modal('show');
    $('#modal-form .modal-title').text('Edit Product');

    $('#modal-form form')[0].reset();
    $('#modal-form form').attr('action', url);
    $('#modal-form [name=_method]').val('put');
    $('#modal-form [name=nama_produk]').focus();

    $.get(url)
        .done((response) => {
            if (response.status && response.data) {
                const product = response.data;
                
                // Set form values with the correct property names from response
                $('#modal-form [name=nama_produk]').val(product.product_name);
                $('#modal-form [name=id_kategori]').val(product.category_id);
                $('#modal-form [name=merk]').val(product.brand);
                $('#modal-form [name=harga_beli]').val(product.purchase_price);
                $('#modal-form [name=harga_jual]').val(product.selling_price);
                $('#modal-form [name=diskon]').val(product.discount);
                $('#modal-form [name=stok]').val(product.stock);
            } else {
                alert('Invalid response format');
            }
        })
        .fail((errors) => {
            console.error('Error fetching product data:', errors);
            alert('Unable to display data. Please try again.');
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

    function deleteSelected(url) {
        if ($('input:checked').length > 1) {
            if (confirm('Yakin ingin menghapus data terpilih?')) {
                $.post(url, $('.form-produk').serialize())
                    .done((response) => {
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Unable to delete data');
                        return;
                    });
            }
        } else {
            alert('Select the data to delete');
            return;
        }
    }

    function cetakBarcode(url) {
        if ($('input:checked').length < 1) {
            alert('Select the data to print');
            return;
        } else if ($('input:checked').length < 3) {
            alert('Select at least 3 data to print');
            return;
        } else {
            $('.form-produk')
                .attr('target', '_blank')
                .attr('action', url)
                .submit();
        }
    }
</script>
@endpush