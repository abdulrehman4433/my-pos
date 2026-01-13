<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post" class="form-horizontal" data-toggle="validator">
            @csrf
            @method('post')

            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                    <h4 class="modal-title"></h4>
                </div>

                <div class="modal-body">

                    {{-- Invoice Reference --}}
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Invoice Reference</label>
                        <div class="col-lg-7">

                            <select name="invoice_reference" id="invoice_reference" onchange="referenceChanged(this)" class="form-control" required>
                                <option value="">-- Select Reference --</option>
                                <option value="product">Product</option>
                                <option value="project">Project</option>
                                <option value="rental">Rental</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    {{-- Reference ID --}}
                    <div class="form-group row" style="display: none;" id="reference_id_group">
                        <label class="col-lg-3 control-label">Select Product</label>
                        <div class="col-lg-7" id="reference_id_container">
                            <!-- Default hidden input -->
                            <input type="hidden" name="reference_id" value="" class="form-control">
                        </div>
                    </div>

                    {{-- Quantity --}}
                    <div class="form-group row" id="quantity_group">
                        <label class="col-lg-3 control-label">Quantity</label>
                        <div class="col-lg-7">
                            <input type="number" step="1" min="1"
                                id="temp_quantity"
                                class="form-control"
                                value="1">
                        </div>
                    </div>

                    {{-- Sub Total --}}
                    <div class="form-group row" id="sub_total_group">
                        <label class="col-lg-3 control-label">Sub Total</label>
                        <div class="col-lg-7">
                            <input type="number" step="0.01" min="0"
                                name="sub_total"
                                class="form-control"
                                value="0"
                                readonly>
                        </div>
                    </div>


                    {{-- Tax Amount --}}
                    <div class="form-group row" style="display: none;">
                        <label class="col-lg-3 control-label">Tax Amount</label>
                        <div class="col-lg-7">
                            <input type="hidden" value="0"
                                   name="tax_amount"
                                   class="form-control">
                        </div>
                    </div>

                    {{-- Discount Amount --}}
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Discount</label>
                        <div class="col-lg-7">
                            <input type="number" step="0.01" min="0"
                                   name="discount_amount"
                                   class="form-control">
                        </div>
                    </div>

                    {{-- Payment Received --}}
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Payment Received</label>
                        <div class="col-lg-7">
                            <select name="payment_method" class="form-control" required>
                                <option value="">-- Select Payment Method --</option>
                                <option value="cash">Cash</option>
                                <option value="easypaisa">EasyPaisa</option>
                                <option value="jazzcash">JazzCash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    {{-- Payment Status --}}
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Payment Status</label>
                        <div class="col-lg-7">
                            <select name="payment_status" class="form-control" required>
                                <option value="">-- Select Status --</option>
                                <option value="paid">Paid</option>
                                <option value="partial">Partial</option>
                                <option value="unpaid">Unpaid</option>
                            </select>
                        </div>
                    </div>

                    {{-- Selected Products Table --}}
                    <div class="form-group row" id="products_table_group" style="display:none;">
                        <div class="col-lg-12">
                            <table class="table table-bordered" id="products_table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th width="120">Unit Price</th>
                                        <th width="100">Qty</th>
                                        <th width="120">Total</th>
                                        <th width="60">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
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
