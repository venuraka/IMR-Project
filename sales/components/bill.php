<div class="card">
    <div class="card-body">
        <div class="bill-header text-center mb-4">
            <h4>POS System</h4>
            <p class="mb-1">Sales Receipt</p>
            <p class="mb-1">Date: <?php echo date('Y-m-d H:i:s'); ?></p>
            <hr>
        </div>

        <div class="bill-info mb-3">
            <div class="row">
                <div class="col-6">
                    <p class="mb-1"><strong>Customer:</strong> <span id="bill-customer">Not Selected</span></p>
                </div>
                <div class="col-6">
                    <p class="mb-1"><strong>Employee:</strong> <span id="bill-employee">Not Selected</span></p>
                </div>
            </div>
        </div>

        <div class="bill-items">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody id="bill-items">
                    <!-- Items will be added here dynamically -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"></td>
                        <td class="text-end"><strong>Subtotal:</strong></td>
                        <td class="text-end" id="subtotal">$0.00</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td class="text-end"><strong>Total:</strong></td>
                        <td class="text-end" id="total">$0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="bill-footer text-center mt-4">
            <p class="mb-1">Thank you for your purchase!</p>
            <p class="mb-1">Please come again</p>
        </div>
    </div>
</div>