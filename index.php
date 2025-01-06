<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Master Data</h5>
                        <a href="supplier.php" class="btn btn-primary mb-2 w-100">Manage Suppliers</a>
                        <a href="category.php" class="btn btn-primary mb-2 w-100">Manage Categories</a>
                        <a href="product.php" class="btn btn-primary mb-2 w-100">Manage Products</a>
                        <a href="employee.php" class="btn btn-primary mb-2 w-100">Manage Employees</a>
                        <a href="customer.php" class="btn btn-primary mb-2 w-100">Manage Customers</a>
                        <a href="regular_customer.php" class="btn btn-primary mb-2 w-100">Manage Regular Customers</a>
                        <a href="stock.php" class="btn btn-primary w-100">Manage Stock</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sales Management</h5>
                        <a href="sales.php" class="btn btn-success w-100">New Sale</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reports</h5>
                        <a href="sale_report.php" class="btn btn-info w-100">Sales Report</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>