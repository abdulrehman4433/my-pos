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
                    {{-- Rental Product --}}
                    <div class="form-group row">
                        <label for="rental_product" class="col-lg-2 col-lg-offset-1 control-label">Product</label>
                        <div class="col-lg-6">
                            <input type="text" name="rental_product" id="rental_product" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Rental Person --}}
                    <div class="form-group row">
                        <label for="rental_person" class="col-lg-2 col-lg-offset-1 control-label">Person</label>
                        <div class="col-lg-6">
                            <input type="text" name="rental_person" id="rental_person" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="form-group row">
                        <label for="rental_person_phone" class="col-lg-2 col-lg-offset-1 control-label">Phone</label>
                        <div class="col-lg-6">
                            <input type="text" name="rental_person_phone" id="rental_person_phone" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="form-group row">
                        <label for="rental_person_address" class="col-lg-2 col-lg-offset-1 control-label">Address</label>
                        <div class="col-lg-6">
                            <textarea name="rental_person_address" id="rental_person_address" rows="3" class="form-control"></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Price --}}
                    <div class="form-group row">
                        <label for="rental_price" class="col-lg-2 col-lg-offset-1 control-label">Price</label>
                        <div class="col-lg-6">
                            <input type="number" name="rental_price" id="rental_price" class="form-control" min="0" step="0.01" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Duration --}}
                    <div class="form-group row">
                        <label for="rental_duration" class="col-lg-2 col-lg-offset-1 control-label">Duration</label>
                        <div class="col-lg-6">
                            <input type="text" name="rental_duration" id="rental_duration" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Start Date --}}
                    <div class="form-group row">
                        <label for="rental_start_date" class="col-lg-2 col-lg-offset-1 control-label">Start Date</label>
                        <div class="col-lg-6">
                            <input type="date" name="rental_start_date" id="rental_start_date" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- End Date --}}
                    <div class="form-group row">
                        <label for="rental_end_date" class="col-lg-2 col-lg-offset-1 control-label">End Date</label>
                        <div class="col-lg-6">
                            <input type="date" name="rental_end_date" id="rental_end_date" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="form-group row">
                        <label for="rental_status" class="col-lg-2 col-lg-offset-1 control-label">Status</label>
                        <div class="col-lg-6">
                            <select name="rental_status" id="rental_status" class="form-control" required>
                                <option value="pending">Pending</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="overdue">Overdue</option>
                            </select>
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
