<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-md" role="document">
        <form action="{{ route('clear-invoice-remaining-amount') }}" method="post" class="form-horizontal" data-toggle="validator">
            @csrf

            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                    <h4 class="modal-title"></h4>
                </div>

                <div class="modal-body">

                    {{-- Invoice --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Invoice</label>
                        <div class="col-lg-7">
                            <input type="text" name="invoice_code" value="" class="form-control" readonly>
                        </div>
                    </div>

                    {{-- Invoice Reference --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Invoice Reference</label>
                        <div class="col-lg-7">
                            <input type="text" name="invoice_reference" class="form-control" value="" readonly>
                        </div>
                    </div>

                    {{-- Received Amount --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Received Amount</label>
                        <div class="col-lg-7">
                            <input type="text" name="received_amount" class="form-control" readonly>
                        </div>
                    </div>

                    {{-- Remaining Amount --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Remaining Amount</label>
                        <div class="col-lg-7">
                            <input type="text" name="remaining_amount" class="form-control" readonly>
                        </div>
                    </div>

                    {{-- Received Amount In --}}
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Amount Received In</label>
                        <div class="col-lg-7">
                            <select name="amount_received_in" class="form-control">
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

                    {{-- invoice id --}}
                    <input type="hidden" name="invoice_id" value="">

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
