<?php
session_start();
include('dbcon.php');
include('include/stock_functions.php');  // Add this line


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

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Sale completed successfully!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add this right after the existing success alert -->
        <?php if (isset($_GET['show_payment'])): ?>
            <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="paymentModalLabel">Payment Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="sale_id" value="<?php echo $_SESSION['last_sale_id'] ?? ''; ?>">
                                <input type="hidden" name="total_amount" value="<?php echo $_SESSION['sale_total'] ?? 0; ?>">
                                <div class="mb-3">
                                    <label for="payment_type" class="form-label">Payment Type</label>
                                    <select name="payment_type" id="payment_type" class="form-control" required>
                                        <option value="">Select Payment Type</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Credit Card">Credit Card</option>
                                        <option value="Debit Card">Debit Card</option>
                                        <option value="Mobile Payment">Mobile Payment</option>
                                    </select>
                                </div>
                                <p>Total Amount: $<?php echo number_format($_SESSION['sale_total'] ?? 0, 2); ?></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Complete Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                    paymentModal.show();
                });
            </script>
        <?php endif; ?>

        <?php if (isset($_GET['payment_success'])): ?>
            <div class="alert alert-success">Payment processed successfully!</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">New Sale</h5>
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer</label>
                            <div class="input-group">
                                <input type="text" id="customer_search" class="form-control" placeholder="Search customer..." autocomplete="off">
                                <input type="hidden" name="customer_id" id="customer_id" required>
                            </div>
                            <div id="customer_search_results" class="dropdown-menu w-100"></div>
                            <div id="selected_customer" class="form-text"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee</label>
                            <div class="input-group">
                                <input type="text" id="employee_search" class="form-control" placeholder="Search employee..." autocomplete="off">
                                <input type="hidden" name="employee_id" id="employee_id" required>
                            </div>
                            <div id="employee_search_results" class="dropdown-menu w-100"></div>
                            <div id="selected_employee" class="form-text"></div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Add Products</h5>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <div class="input-group">
                                        <input type="text" id="product_search" class="form-control" placeholder="Search products..." autocomplete="off">
                                        <button type="button" class="btn btn-primary" id="add_product">Add Product</button>
                                    </div>
                                    <div id="product_search_results" class="dropdown-menu w-100"></div>
                                </div>
                            </div>

                            <table class="table" id="selected_products_table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Selected products will be added here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Replace the existing Sale Summary card with this new bill format -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="bill-header text-center mb-4">
                                        <h4>POS System</h4>
                                        <p class="mb-1">Sales Receipt</p>
                                        <p class="mb-1">Date: <?php echo date('Y-m-d H:i:s'); ?></p>
                                        <hr>
                                    </div>

                                    <div class="bill-info mb-3">
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>Customer:</strong> <span id="bill-customer">Not Selected</span></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1"><strong>Employee:</strong> <span id="bill-employee">Not Selected</span></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bill-items">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-end">Price</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="bill-items">
                                                <!-- Items will be added here dynamically -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2"></td>
                                                    <td class="text-end"><strong>Subtotal:</strong></td>
                                                    <td class="text-end" id="subtotal">$0.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"></td>
                                                    <td class="text-end"><strong>Total:</strong></td>
                                                    <td class="text-end" id="total">$0.00</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="bill-footer text-center mt-4">
                                        <p class="mb-1">Thank you for your purchase!</p>
                                        <p class="mb-1">Please come again</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Complete Sale</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInputs = document.querySelectorAll('input[type="number"]');

            function updateTotals() {
                let subtotal = 0;
                let totalItems = 0;
                const billItems = document.getElementById('bill-items');
                billItems.innerHTML = ''; // Clear existing items

                const rows = selectedProductsTable.getElementsByTagName('tr');
                for (let row of rows) {
                    const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
                    const price = parseFloat(row.querySelector('td:nth-child(2)').textContent.replace('$', ''));
                    const rowSubtotal = quantity * price;

                    // Update bill items
                    if (quantity > 0) {
                        const itemRow = document.createElement('tr');
                        itemRow.innerHTML = `
                            <td>${row.querySelector('td:nth-child(1)').textContent}</td>
                            <td class="text-center">${quantity}</td>
                            <td class="text-end">$${price.toFixed(2)}</td>
                            <td class="text-end">$${rowSubtotal.toFixed(2)}</td>
                        `;
                        billItems.appendChild(itemRow);
                    }

                    subtotal += rowSubtotal;
                    totalItems += quantity;
                }

                // Update all totals 
                document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
                document.getElementById('total').textContent = '$' + subtotal.toFixed(2); // Total is same as subtotal
                document.getElementById('totalItems').textContent = totalItems;
            }

            quantityInputs.forEach(input => {
                input.addEventListener('change', updateTotals);
                input.addEventListener('keyup', updateTotals);
            });

            // Customer search
            let customerTimeout = null;
            const customerSearch = document.getElementById('customer_search');
            const customerResults = document.getElementById('customer_search_results');
            const customerId = document.getElementById('customer_id');
            const selectedCustomer = document.getElementById('selected_customer');

            customerSearch.addEventListener('input', function() {
                clearTimeout(customerTimeout);
                customerTimeout = setTimeout(() => {
                    const search = this.value;
                    if (search.length < 2) {
                        customerResults.classList.remove('show');
                        return;
                    }

                    fetch(`sales.php?search_customer=${encodeURIComponent(search)}`)
                        .then(response => response.json())
                        .then(data => {
                            customerResults.innerHTML = '';
                            data.forEach(customer => {
                                const div = document.createElement('div');
                                div.className = 'dropdown-item';
                                div.textContent = `${customer.FirstName} ${customer.LastName}`;
                                div.onclick = function() {
                                    customerId.value = customer.CustomerID;
                                    customerSearch.value = `${customer.FirstName} ${customer.LastName}`;
                                    selectedCustomer.textContent = `Selected: ${customer.FirstName} ${customer.LastName}`;
                                    customerResults.classList.remove('show');
                                    document.getElementById('bill-customer').textContent = `${customer.FirstName} ${customer.LastName}`;
                                };
                                customerResults.appendChild(div);
                            });
                            customerResults.classList.add('show');
                        });
                }, 300);
            });

            // Employee search
            let employeeTimeout = null;
            const employeeSearch = document.getElementById('employee_search');
            const employeeResults = document.getElementById('employee_search_results');
            const employeeId = document.getElementById('employee_id');
            const selectedEmployee = document.getElementById('selected_employee');

            employeeSearch.addEventListener('input', function() {
                clearTimeout(employeeTimeout);
                employeeTimeout = setTimeout(() => {
                    const search = this.value;
                    if (search.length < 2) {
                        employeeResults.classList.remove('show');
                        return;
                    }

                    fetch(`sales.php?search_employee=${encodeURIComponent(search)}`)
                        .then(response => response.json())
                        .then(data => {
                            employeeResults.innerHTML = '';
                            data.forEach(employee => {
                                const div = document.createElement('div');
                                div.className = 'dropdown-item';
                                div.textContent = `${employee.FirstName} ${employee.LastName}`;
                                div.onclick = function() {
                                    employeeId.value = employee.EmployeeId;
                                    employeeSearch.value = `${employee.FirstName} ${employee.LastName}`;
                                    selectedEmployee.textContent = `Selected: ${employee.FirstName} ${employee.LastName}`;
                                    employeeResults.classList.remove('show');
                                    document.getElementById('bill-employee').textContent = `${employee.FirstName} ${employee.LastName}`;
                                };
                                employeeResults.appendChild(div);
                            });
                            employeeResults.classList.add('show');
                        });
                }, 300);
            });

            // Product search and add functionality
            const productSearch = document.getElementById('product_search');
            const productResults = document.getElementById('product_search_results');
            const selectedProductsTable = document.getElementById('selected_products_table').getElementsByTagName('tbody')[0];
            const addProductBtn = document.getElementById('add_product');
            let selectedProduct = null;

            productSearch.addEventListener('input', function() {
                const search = this.value;
                if (search.length < 2) {
                    productResults.classList.remove('show');
                    return;
                }

                fetch(`sales.php?search_product=${encodeURIComponent(search)}`)
                    .then(response => response.json())
                    .then(data => {
                        productResults.innerHTML = '';
                        data.forEach(product => {
                            const div = document.createElement('div');
                            div.className = 'dropdown-item';
                            div.textContent = `${product.ProductName} - $${product.Price}`;
                            div.onclick = function() {
                                selectedProduct = product;
                                productSearch.value = product.ProductName;
                                productResults.classList.remove('show');
                            };
                            productResults.appendChild(div);
                        });
                        productResults.classList.add('show');
                    });
            });

            addProductBtn.addEventListener('click', function() {
                if (!selectedProduct) return;

                // Check if product already exists in table
                const existingRows = selectedProductsTable.getElementsByTagName('tr');
                for (let row of existingRows) {
                    if (row.dataset.productId === selectedProduct.ProductID) {
                        alert('This product is already added!');
                        return;
                    }
                }

                const row = selectedProductsTable.insertRow();
                row.dataset.productId = selectedProduct.ProductID;
                row.innerHTML = `
                    <td>${selectedProduct.ProductName}</td>
                    <td>$${selectedProduct.Price}</td>
                    <td>
                        <input type="number" name="products[${selectedProduct.ProductID}]" 
                            class="form-control quantity-input" value="1" min="1">
                    </td>
                    <td>$${selectedProduct.Price}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-product">Remove</button>
                    </td>
                `;

                // Add event listeners for quantity change and remove
                const quantityInput = row.querySelector('.quantity-input');
                quantityInput.addEventListener('change', updateTotals);
                quantityInput.addEventListener('keyup', updateTotals);

                row.querySelector('.remove-product').addEventListener('click', function() {
                    row.remove();
                    updateTotals();
                });

                // Clear selection
                selectedProduct = null;
                productSearch.value = '';
                updateTotals();
            });

            // Update the updateTotals function
            function updateTotals() {
                let subtotal = 0;
                let totalItems = 0;
                const billItems = document.getElementById('bill-items');
                billItems.innerHTML = ''; // Clear existing items

                const rows = selectedProductsTable.getElementsByTagName('tr');
                for (let row of rows) {
                    const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
                    const price = parseFloat(row.querySelector('td:nth-child(2)').textContent.replace('$', ''));
                    const rowSubtotal = quantity * price;

                    // Update bill items
                    if (quantity > 0) {
                        const itemRow = document.createElement('tr');
                        itemRow.innerHTML = `
                            <td>${row.querySelector('td:nth-child(1)').textContent}</td>
                            <td class="text-center">${quantity}</td>
                            <td class="text-end">$${price.toFixed(2)}</td>
                            <td class="text-end">$${rowSubtotal.toFixed(2)}</td>
                        `;
                        billItems.appendChild(itemRow);
                    }

                    subtotal += rowSubtotal;
                    totalItems += quantity;
                }


                const total = subtotal;

                // Update all totals
                document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
                document.getElementById('total').textContent = '$' + total.toFixed(2);
                document.getElementById('totalItems').textContent = totalItems;
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
                    customerResults.classList.remove('show');
                }
                if (!employeeSearch.contains(e.target) && !employeeResults.contains(e.target)) {
                    employeeResults.classList.remove('show');
                }
                if (!productSearch.contains(e.target) && !productResults.contains(e.target)) {
                    productResults.classList.remove('show');
                }
            });
        });
    </script>
    <style>
        .dropdown-menu {
            display: none;
            position: absolute;
            z-index: 1000;
            background-color: white;
            border: 1px solid #ddd;
            max-height: 200px;
            overflow-y: auto;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .quantity-input {
            width: 80px;
        }

        .remove-product {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .bill-header {
            border-bottom: 1px solid #ddd;
        }

        padding-bottom: 10px;
        }

        .bill-info {
            font-size: 0.9rem;
        }

        .bill-items {
            margin: 20px 0;
        }

        .bill-footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 0.9rem;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.3rem;
        }
    </style>
</body>

</html>