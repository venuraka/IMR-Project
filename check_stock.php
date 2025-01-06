<?php
include('dbcon.php');
include('include/stock_functions.php');

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $stock = getProductStock($conn, $product_id);
    echo json_encode(['stock' => $stock]);
    exit();
}
