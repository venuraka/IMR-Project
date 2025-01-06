<?php
include('dbcon.php');

$where_clause = "WHERE 1=1";
$params = [];
$param_types = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET)) {
    if (!empty($_GET['start_date'])) {
        $where_clause .= " AND s.SaleDate >= ?";
        $params[] = $_GET['start_date'];
        $param_types .= "s";
    }
    if (!empty($_GET['end_date'])) {
        $where_clause .= " AND s.SaleDate <= ?";
        $params[] = $_GET['end_date'];
        $param_types .= "s";
    }
    if (!empty($_GET['customer'])) {
        $where_clause .= " AND (c.FirstName LIKE ? OR c.LastName LIKE ?)";
        $search_term = "%" . $_GET['customer'] . "%";
        $params[] = $search_term;
        $params[] = $search_term;
        $param_types .= "ss";
    }
    if (!empty($_GET['employee'])) {
        $where_clause .= " AND (e.FirstName LIKE ? OR e.LastName LIKE ?)";
        $search_term = "%" . $_GET['employee'] . "%";
        $params[] = $search_term;
        $params[] = $search_term;
        $param_types .= "ss";
    }
}

// Update the query to ensure we get non-null values and proper grouping
$query = "
    SELECT 
        s.SaleID,
        s.SaleDate,
        s.TotalAmount,
        CONCAT(COALESCE(c.FirstName, ''), ' ', COALESCE(c.LastName, '')) as CustomerName,
        CONCAT(COALESCE(e.FirstName, ''), ' ', COALESCE(e.LastName, '')) as EmployeeName,
        GROUP_CONCAT(
            CONCAT(
                COALESCE(p.ProductName, 'Unknown Product'),
                ' (',
                COALESCE(sd.Quantity, 0),
                ' × $',
                COALESCE(p.Price, 0.00),
                ' = $',
                COALESCE(sd.Subtotal, 0.00),
                ')'
            )
            SEPARATOR ', '
        ) as Products
    FROM Sale s
    LEFT JOIN Customer c ON s.CustomerID = c.CustomerID
    LEFT JOIN Employee e ON s.EmployeeID = e.EmployeeId
    LEFT JOIN SaleDetail sd ON s.SaleID = sd.SaleID
    LEFT JOIN Product p ON sd.ProductID = p.ProductID
    $where_clause
    GROUP BY s.SaleID, s.SaleDate, s.TotalAmount, CustomerName, EmployeeName
    ORDER BY s.SaleDate DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$sales = $stmt->get_result();

// Calculate totals
$total_revenue = 0;
$total_sales = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-primary">Back to Home</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Search Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Search Filters</h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $_GET['start_date'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $_GET['end_date'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" name="customer" class="form-control" value="<?php echo $_GET['customer'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employee Name</label>
                        <input type="text" name="employee" class="form-control" value="<?php echo $_GET['employee'] ?? ''; ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="sale_report.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sales Summary -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Total Revenue: $<span id="total-revenue">0.00</span></h5>
                    </div>
                    <div class="col-md-6">
                        <h5>Total Sales: <span id="total-sales">0</span></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Sales Details</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Employee</th>
                                <th>Products</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_revenue = 0;
                            $total_sales = 0;

                            while ($sale = $sales->fetch_assoc()):
                                $total_revenue += floatval($sale['TotalAmount']);
                                $total_sales++;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sale['SaleID'] ?? ''); ?></td>
                                    <td><?php echo $sale['SaleDate'] ? date('Y-m-d', strtotime($sale['SaleDate'])) : ''; ?></td>
                                    <td><?php echo htmlspecialchars($sale['CustomerName'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($sale['EmployeeName'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($sale['Products'] ?? 'No products'); ?></td>
                                    <td>$<?php echo number_format(floatval($sale['TotalAmount']), 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('total-revenue').textContent = '<?php echo number_format($total_revenue, 2); ?>';
        document.getElementById('total-sales').textContent = '<?php echo $total_sales; ?>';
    </script>
</body>

</html>