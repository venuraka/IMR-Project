<?php
include 'dbcon.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bill.css" rel="stylesheet">
</head>

<body>
    <div class="search-container">
        <div class="container">
            <h1 class="text-white text-center mb-4">Customer Bill Search</h1>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card search-card">
                        <div class="card-body p-4">
                            <form method="POST" action="">
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control form-control-lg"
                                        placeholder="Enter Sale ID" name="sale_id"
                                        required min="1">
                                    <button class="btn btn-primary btn-search" type="submit">
                                        Search
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sale_id'])) {
            $sale_id = $_POST['sale_id'];

            // Query to get sale details
            $sale_query = $sale_query = "SELECT s.*, c.FirstName, c.LastName, c.Contact, c.Address, 
    e.FirstName as EmpFirstName, e.LastName as EmpLastName
FROM Sale s 
LEFT JOIN Customer c ON s.CustomerID = c.CustomerID
LEFT JOIN Employee e ON s.EmployeeID = e.EmployeeID
WHERE s.SaleID = ?";


            // Prepare and execute the query
            $stmt = sqlsrv_query($conn, $sale_query, array(&$sale_id));

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            // Fetch the result
            $sale_data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

            if ($sale_data) {
                // Query to get products in the sale
                $products_query = "SELECT p.*, sd.Quantity, sd.Subtotal 
                           FROM ProductSale ps
                           JOIN Product p ON ps.ProductID = p.ProductID
                           JOIN SaleDetail sd ON ps.SaleID = sd.SaleID
                           WHERE ps.SaleID = ?";

                // Prepare and execute the query for products
                $stmt = sqlsrv_query($conn, $products_query, array(&$sale_id));

                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
        ?>

                <!-- Your HTML for displaying the bill -->
                <div class="bill-container">
                    <!-- Add your HTML structure here -->
                    <div class="bill-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Bill #<?php echo $sale_id; ?></h4>
                                <p>Date: <?php echo $sale_data['SaleDate']->format('d/m/Y'); ?></p>


                            </div>
                            <div class="col-md-6 text-end">
                                <h5>Customer Details:</h5>
                                <p><?php echo $sale_data['FirstName'] . ' ' . $sale_data['LastName']; ?><br>
                                    <?php echo $sale_data['Address']; ?><br>
                                    Contact: <?php echo $sale_data['Contact']; ?></p>
                            </div>
                        </div>
                    </div>

                    <table class="table bill-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?php echo $product['ProductName']; ?></td>
                                    <td>$<?php echo number_format($product['Price'], 2); ?></td>
                                    <td><?php echo $product['Quantity']; ?></td>
                                    <td class="text-end">$<?php echo number_format($product['Subtotal'], 2); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="total-section">
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Total Amount:</strong></td>
                                        <td class="text-end"><strong>$<?php echo number_format($sale_data['TotalAmount'], 2); ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

        <?php
            } else {
                echo '<div class="alert alert-warning">No sale found with ID: ' . $sale_id . '</div>';
            }
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>