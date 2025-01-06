<?php
session_start();
include('dbcon.php');
include('include/stock_functions.php');


$products = mysqli_query($conn, "SELECT * FROM Product");
$customers = mysqli_query($conn, "SELECT * FROM Customer");
$employees = mysqli_query($conn, "SELECT * FROM Employee");

if (isset($_GET['search_customer'])) {
    $search = $_GET['search_customer'];
    $query = "SELECT CustomerID, FirstName, LastName FROM Customer 
              WHERE FirstName LIKE ? OR LastName LIKE ? LIMIT 5";
    $stmt = $conn->prepare($query);
    $search = "%$search%";
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    echo json_encode($customers);
    exit();
}

if (isset($_GET['search_employee'])) {
    $search = $_GET['search_employee'];
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
    exit();
}

if (isset($_GET['search_product'])) {
    $search = $_GET['search_product'];
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
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['payment_type']) && isset($_POST['sale_id']) && isset($_POST['total_amount'])) {
        // Handle payment submission
        $payment_type = $_POST['payment_type'];
        $sale_id = $_POST['sale_id'];
        $total_amount = $_POST['total_amount'];
        $payment_date = date('Y-m-d');

        // Insert payment record
        $sql = "INSERT INTO Payment (PaymentType, PaymentDate, PaymentAmount, SaleID) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdi", $payment_type, $payment_date, $total_amount, $sale_id);
        $stmt->execute();

        // Clear the session
        unset($_SESSION['last_sale_id']);
        unset($_SESSION['sale_total']);

        header('Location: sales.php?payment_success=1');
        exit();
    } else if (isset($_POST['customer_id']) && isset($_POST['employee_id']) && isset($_POST['products'])) {
        // Validate stock levels first
        $stockErrors = validateStock($conn, $_POST['products']);

        if (!empty($stockErrors)) {
            $error = implode("<br>", $stockErrors);
        } else {
            mysqli_begin_transaction($conn);

            try {
                $customer_id = $_POST['customer_id'];
                $employee_id = $_POST['employee_id'];
                $sale_date = date('Y-m-d');
                $total_amount = 0;

                $sql = "INSERT INTO Sale (CustomerID, EmployeeID, SaleDate, TotalAmount) VALUES (?, ?, ?, 0)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $customer_id, $employee_id, $sale_date);
                $stmt->execute();

                $sale_id = $conn->insert_id;

                // Calculate total amount from products
                foreach ($_POST['products'] as $product_id => $quantity) {
                    if ($quantity > 0) {
                        $price_sql = "SELECT Price FROM Product WHERE ProductID = ?";
                        $price_stmt = $conn->prepare($price_sql);
                        $price_stmt->bind_param("i", $product_id);
                        $price_stmt->execute();
                        $result = $price_stmt->get_result();
                        $product = $result->fetch_assoc();
                        $subtotal = $product['Price'] * $quantity;
                        $total_amount += $subtotal;

                        // Insert into SaleDetail
                        $sql = "INSERT INTO SaleDetail (SaleID, ProductID, Quantity, Subtotal) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iiid", $sale_id, $product_id, $quantity, $subtotal);
                        $stmt->execute();

                        // Insert into ProductSale
                        $sql = "INSERT INTO ProductSale (SaleID, ProductID) VALUES (?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ii", $sale_id, $product_id);
                        $stmt->execute();

                        // Update stock levels - replace the old update query with this new one
                        $stockUpdate = "
                            UPDATE StockProduct 
                            SET Quantity = Quantity - ?
                            WHERE ProductID = ? 
                            AND Quantity >= ?
                            AND StockID = (
                                SELECT StockID 
                                FROM (
                                    SELECT sp.StockID
                                    FROM StockProduct sp
                                    WHERE sp.ProductID = ?
                                    AND sp.Quantity >= ?
                                    ORDER BY sp.StockID ASC
                                    LIMIT 1
                                ) AS subquery
                            )";

                        $stmt = $conn->prepare($stockUpdate);
                        $stmt->bind_param("iiiii", $quantity, $product_id, $quantity, $product_id, $quantity);
                        $stmt->execute();

                        if ($stmt->affected_rows == 0) {
                            throw new Exception("Failed to update stock for product ID: $product_id");
                        }
                    }
                }

                mysqli_commit($conn);
                $_SESSION['last_sale_id'] = $sale_id;
                $_SESSION['sale_total'] = $total_amount;

                header('Location: sales.php?success=1&show_payment=1');
                exit();
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="sales/css/sales.css">
</head>

<body>
    <?php include('sales/components/navbar.php'); ?>

    <div class="container">
        <?php
        if (isset($_GET['success']) || isset($error) || isset($_GET['show_payment'])) {
            include('sales/components/alerts.php');
        }
        include('sales/components/payment_modal.php');
        ?>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">New Sale</h5>
                <form method="POST">
                    <div class="row mb-3">
                        <?php
                        include('sales/components/customer_search.php');
                        include('sales/components/employee_search.php');
                        ?>
                    </div>

                    <?php include('sales/components/product_search.php'); ?>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <?php include('sales/components/bill.php'); ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Complete Sale</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="sales/js/sales.js"></script>
    <script src="sales/js/customer.js"></script>
    <script src="sales/js/employee.js"></script>
    <script src="sales/js/product.js"></script>
</body>

</html>