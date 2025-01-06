<?php
require_once '../../dbcon.php';

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "SELECT EmployeeId, FirstName, LastName FROM Employee 
              WHERE FirstName LIKE ? OR LastName LIKE ? LIMIT 5";
    $stmt = $conn->prepare($query);
    $search = "%$search%";
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    echo json_encode($employees);
}
