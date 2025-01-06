<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Sale completed successfully!</div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (isset($_GET['payment_success'])): ?>
    <div class="alert alert-success">Payment processed successfully!</div>
<?php endif; ?>