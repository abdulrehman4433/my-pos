<!-- Discount Calculator Modal -->
<div class="modal fade" id="discountCalculatorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Discount Calculator</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label>Total Amount</label>
                    <input type="number" step="0.01" id="calc_amount" class="form-control">
                </div>

                <div class="form-group">
                    <label>Discount Amount</label>
                    <input type="number" step="0.01" id="calc_discount" class="form-control">
                </div>

                <div class="form-group">
                    <label>Discount Percentage (%)</label>
                    <input type="number" step="0.01" id="calc_percentage" class="form-control" readonly>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="apply_discount">
                    Apply Discount
                </button>

                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>