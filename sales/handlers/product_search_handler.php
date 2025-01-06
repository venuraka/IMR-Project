<?php
require_once '../../dbcon.php';

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "SELECT ProductID, ProductName, Price FROM Product 
              WHERE ProductName LIKE ? LIMIT 5";
    $stmt = $conn->prepare($query);
    $search = "%$search%";
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode($products);
}
