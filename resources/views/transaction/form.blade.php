<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-md" role="document">
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

                    {{-- Return No --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Return No</label>
                        <div class="col-lg-7">
                            <input type="text"
                                   name="return_no"
                                   class="form-control"
                                   value="RTN-{{ date('hms') }}"
                                   readonly>
                        </div>
                    </div>

                    {{-- Invoice --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Invoice</label>
                        <div class="col-lg-7">
                            <select name="invoice_id" id="invoice_id" class="form-control" required>
                                <option value="">-- Select Invoice --</option>
                                @foreach ($invoices ?? [] as $invoice)
                                    <option value="{{ $invoice->id }}">
                                        {{ $invoice->invoice_code }} | {{ $invoice->invoice_reference }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Invoice Grand Total --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Invoice Grand Total</label>
                        <div class="col-lg-7">
                            <input type="text"
                                   name="invoice_grand_total"
                                   class="form-control"
                                   readonly>
                        </div>
                    </div>

                    {{-- Return Type --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Return Type</label>
                        <div class="col-lg-7">
                            <select name="return_type" id="return_type" class="form-control" required>
                                <option value="">-- Select Type --</option>
                                <option value="full">Full Return</option>
                                <option value="partial">Partial Return</option>
                            </select>
                        </div>
                    </div>

                    {{-- Return Amount --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Return Amount</label>
                        <div class="col-lg-7">
                            <input type="text"
                                   name="return_amount"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    {{-- Return Amount In --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Return Amount In</label>
                        <div class="col-lg-7">
                            <select name="return_amount_in" class="form-control">
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

                    {{-- Reason --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Reason</label>
                        <div class="col-lg-7">
                            <textarea name="reason"
                                      rows="3"
                                      class="form-control"
                                      placeholder="Reason for return (optional)"></textarea>
                        </div>
                    </div>

                    {{-- Invoice Items --}}
                    <div class="form-group row" id="invoice-items-wrapper" style="display:none;">
                        <label class="col-lg-4 control-label">Invoice Items</label>
                        <div class="col-lg-7">
                            <table class="table table-bordered table-sm" id="invoice-items-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th width="80">Qty</th>
                                        <th width="120">Return Qty</th>
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
