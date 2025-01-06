<?php

function getProductStock($conn, $productId)
{
    $sql = "SELECT COALESCE(SUM(sp.Quantity), 0) as total_stock
            FROM StockProduct sp
            WHERE sp.ProductID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Get total sold
    $sql = "SELECT COALESCE(SUM(sd.Quantity), 0) as total_sold
            FROM SaleDetail sd
            WHERE sd.ProductID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $sold = $result->fetch_assoc();

    return $row['total_stock'] - $sold['total_sold'];
}

function validateStock($conn, $products)
{
    $errors = [];
    foreach ($products as $productId => $quantity) {
        $available = getProductStock($conn, $productId);
        if ($quantity > $available) {
            // Get product name
            $stmt = $conn->prepare("SELECT ProductName FROM Product WHERE ProductID = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            $errors[] = "Insufficient stock for {$product['ProductName']}. Available: $available, Requested: $quantity";
        }
    }
    return $errors;
}
