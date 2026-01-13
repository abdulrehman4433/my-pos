<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post" class="form-horizontal" data-toggle="validator">
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

                    {{-- Project Name --}}
                    <div class="form-group row">
                        <label for="project_name" class="col-lg-3 control-label">
                            Project Name
                        </label>
                        <div class="col-lg-7">
                            <input type="text"
                                   name="project_name"
                                   id="project_name"
                                   class="form-control"
                                   placeholder="Project Name"
                                   required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Project Phone --}}
                    <div class="form-group row">
                        <label for="project_phone" class="col-lg-3 control-label">
                            Phone
                        </label>
                        <div class="col-lg-7">
                            <input type="text"
                                   name="project_phone"
                                   id="project_phone"
                                   class="form-control"
                                   placeholder="+1 234 567 890"
                                   required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Project Address --}}
                    <div class="form-group row">
                        <label for="project_address" class="col-lg-3 control-label">
                            Address
                        </label>
                        <div class="col-lg-7">
                            <textarea name="project_address"
                                      id="project_address"
                                      rows="2"
                                      class="form-control"
                                      placeholder="Project Address"></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Project Price --}}
                    <div class="form-group row">
                        <label for="project_price" class="col-lg-3 control-label">
                            Project Price
                        </label>
                        <div class="col-lg-7">
                            <input type="number"
                                   name="project_price"
                                   id="project_price"
                                   class="form-control"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Project Duration --}}
                    <div class="form-group row">
                        <label for="project_duration" class="col-lg-3 control-label">
                            Duration
                        </label>
                        <div class="col-lg-7">
                            <input type="text"
                                   name="project_duration"
                                   id="project_duration"
                                   class="form-control"
                                   placeholder="e.g. 6 months">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Project Details --}}
                    <div class="form-group row">
                        <label for="project_details" class="col-lg-3 control-label">
                            Details
                        </label>
                        <div class="col-lg-7">
                            <textarea name="project_details"
                                      id="project_details"
                                      rows="3"
                                      class="form-control"
                                      placeholder="Project description / notes"></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    {{-- Project Status --}}
                    <div class="form-group row">
                        <label for="project_status" class="col-lg-3 control-label">
                            Status
                        </label>
                        <div class="col-lg-7">
                            <select name="project_status"
                                    id="project_status"
                                    class="form-control"
                                    required>
                                <option value="">-- Select Status --</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="on_hold">On Hold</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-success">
                        <i class="fa fa-save"></i> Save
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-flat btn-danger"
                            data-dismiss="modal">
                        <i class="fa fa-arrow-circle-left"></i> Cancel
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
