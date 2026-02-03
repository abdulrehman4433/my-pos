<!-- filepath: c:\xampp\htdocs\my-pos\resources\views\produk\stock-form.blade.php -->
<div class="modal fade" id="modal-stock-form" tabindex="-1" role="dialog" aria-labelledby="modal-stock-form">
    <div class="modal-dialog" role="document">
        <form action="" method="post" class="form-horizontal">
            @csrf
            @method('put')

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Update Product Stock</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="product_display" class="col-lg-3 control-label">Product</label>
                        <div class="col-lg-8">
                            <input type="text" id="product_display" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="product_code_display" class="col-lg-3 control-label">Code</label>
                        <div class="col-lg-8">
                            <input type="text" id="product_code_display" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="stock_old" class="col-lg-3 control-label">Current Stock</label>
                        <div class="col-lg-8">
                            <input type="text" id="stock_old" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="stock" class="col-lg-3 control-label">New Stock</label>
                        <div class="col-lg-8">
                            <input type="number" name="stock" id="stock" class="form-control" required min="0">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="minimum_stock" class="col-lg-3 control-label">Minimum Stock</label>
                        <div class="col-lg-8">
                            <input type="number" name="minimum_stock" id="minimum_stock" class="form-control" required min="0">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-flat btn-success">
                        <i class="fa fa-save"></i> Update Stock
                    </button>
                    <button type="button" class="btn btn-sm btn-flat btn-danger" data-dismiss="modal">
                        <i class="fa fa-arrow-circle-left"></i> Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>