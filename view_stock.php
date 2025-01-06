<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stock = mysqli_query($conn, "
        SELECT s.*, 
               e.FirstName as EmployeeName, 
               e.LastName as EmployeeLastName
        FROM Stock s
        LEFT JOIN Employee e ON s.EmployeeID = e.EmployeeId
        WHERE s.StockID = $id
    ")->fetch_assoc();

    $products = mysqli_query($conn, "
        SELECT p.*, sp.Quantity
        FROM StockProduct sp
        JOIN Product p ON sp.ProductID = p.ProductID
        WHERE sp.StockID = $id
    ");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Stock Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
            <div class="ms-auto">
                <a href="stock.php" class="btn btn-outline-primary">Back to Stock</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Stock Entry Details</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Date:</strong> <?php echo $stock['Date']; ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Employee:</strong> <?php echo htmlspecialchars($stock['EmployeeName'] . ' ' . $stock['EmployeeLastName']); ?>
                    </div>
                </div>

                <h6>Products</h6>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                                <td><?php echo $product['Quantity']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>