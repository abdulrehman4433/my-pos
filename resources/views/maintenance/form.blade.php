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
                    {{-- Name --}}
                    <div class="form-group row">
                        <label for="maintenance_name" class="col-lg-2 col-lg-offset-1 control-label">Name</label>
                        <div class="col-lg-6">
                            <input type="text" name="maintenance_name" id="maintenance_name" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Telephone --}}
                    <div class="form-group row">
                        <label for="maintenance_phone" class="col-lg-2 col-lg-offset-1 control-label">Telephone</label>
                        <div class="col-lg-6">
                            <input type="text" name="maintenance_phone" id="maintenance_phone" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="form-group row">
                        <label for="maintenance_address" class="col-lg-2 col-lg-offset-1 control-label">Address</label>
                        <div class="col-lg-6">
                            <textarea name="maintenance_address" id="maintenance_address" rows="3" class="form-control"></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Price --}}
                    <div class="form-group row">
                        <label for="maintenance_price" class="col-lg-2 col-lg-offset-1 control-label">Price</label>
                        <div class="col-lg-6">
                            <input type="number" name="maintenance_price" id="maintenance_price" class="form-control" required min="0" step="0.01">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Duration --}}
                    <div class="form-group row">
                        <label for="maintenance_duration" class="col-lg-2 col-lg-offset-1 control-label">Duration</label>
                        <div class="col-lg-6">
                            <input type="text" name="maintenance_duration" id="maintenance_duration" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Details --}}
                    <div class="form-group row">
                        <label for="maintenance_details" class="col-lg-2 col-lg-offset-1 control-label">Details</label>
                        <div class="col-lg-6">
                            <textarea name="maintenance_details" id="maintenance_details" rows="3" class="form-control"></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="form-group row">
                        <label class="col-lg-2 col-lg-offset-1 control-label">Active</label>
                        <div class="col-lg-6">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" id="is_active" value="1" checked> Yes
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-success"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-sm btn-flat btn-danger" data-dismiss="modal">
                        <i class="fa fa-arrow-circle-left"></i> Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
