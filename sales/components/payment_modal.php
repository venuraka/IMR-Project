<?php if (isset($_GET['show_payment'])): ?>
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="sale_id" value="<?php echo $_SESSION['last_sale_id'] ?? ''; ?>">
                        <input type="hidden" name="total_amount" value="<?php echo $_SESSION['sale_total'] ?? 0; ?>">
                        <div class="mb-3">
                            <label for="payment_type" class="form-label">Payment Type</label>
                            <select name="payment_type" id="payment_type" class="form-control" required>
                                <option value="">Select Payment Type</option>
                                <option value="Cash">Cash</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="Mobile Payment">Mobile Payment</option>
                            </select>
                        </div>
                        <p>Total Amount: $<?php echo number_format($_SESSION['sale_total'] ?? 0, 2); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Complete Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>