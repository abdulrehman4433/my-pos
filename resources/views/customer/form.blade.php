<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post" class="form-horizontal">
            @csrf
            @method('post')

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"></h4>
                </div>

                <div class="modal-body">
                    {{-- Customer Code --}}
                    <div class="form-group row">
                        <label for="customer_code" class="col-lg-2 col-lg-offset-1 control-label">Customer Code</label>
                        <div class="col-lg-6">
                            <input type="text" name="customer_code" id="customer_code" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Name --}}
                    <div class="form-group row">
                        <label for="name" class="col-lg-2 col-lg-offset-1 control-label">Name</label>
                        <div class="col-lg-6">
                            <input type="text" name="name" id="name" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="form-group row">
                        <label for="phone" class="col-lg-2 col-lg-offset-1 control-label">Phone</label>
                        <div class="col-lg-6">
                            <input type="text" name="phone" id="phone" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="form-group row">
                        <label for="address" class="col-lg-2 col-lg-offset-1 control-label">Address</label>
                        <div class="col-lg-6">
                            <textarea name="address" id="address" rows="3" class="form-control"></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Current Balance --}}
                    <div class="form-group row">
                        <label for="current_balance" class="col-lg-2 col-lg-offset-1 control-label">Current Balance</label>
                        <div class="col-lg-6">
                            <input type="number" name="current_balance" id="current_balance" class="form-control" value="0" step="0.01">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Discount --}}
                    <div class="form-group row">
                        <label for="discount" class="col-lg-2 col-lg-offset-1 control-label">Discount (%)</label>
                        <div class="col-lg-6">
                            <input type="number" name="discount" id="discount" class="form-control" value="0" step="0.01">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="form-group row">
                        <label for="is_active" class="col-lg-2 col-lg-offset-1 control-label">Status</label>
                        <div class="col-lg-6">
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="form-group row">
                        <label for="notes" class="col-lg-2 col-lg-offset-1 control-label">Notes</label>
                        <div class="col-lg-6">
                            <textarea name="notes" id="notes" rows="2" class="form-control"></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-success">
                        <i class="fa fa-save"></i> Save
                    </button>
                    <button type="button" class="btn btn-sm btn-flat btn-danger" data-dismiss="modal">
                        <i class="fa fa-arrow-circle-left"></i> Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
