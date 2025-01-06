<?php
include('dbcon.php');

// Get all products
$products = mysqli_query($conn, "SELECT * FROM Product");

// Get all employees
$employees = mysqli_query($conn, "SELECT * FROM Employee");

// Update the stock query to include accurate product information
$stocks = mysqli_query($conn, "
    SELECT 
        s.*, 
        e.FirstName as EmployeeName, 
        e.LastName as EmployeeLastName,
        GROUP_CONCAT(
            CONCAT(p.ProductName, ' (', COALESCE(sp.Quantity, 0), ')') 
            SEPARATOR ', '
        ) as Products
    FROM Stock s
    LEFT JOIN Employee e ON s.EmployeeID = e.EmployeeId
    LEFT JOIN StockProduct sp ON s.StockID = sp.StockID
    LEFT JOIN Product p ON sp.ProductID = p.ProductID
    GROUP BY s.StockID, s.Date, s.EmployeeID, 
             e.FirstName, e.LastName
    ORDER BY s.Date DESC
");

// Add this new endpoint for product search
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
    $date = $_POST['date'];
    $employee_id = $_POST['employee_id'];
    $products_stock = $_POST['products'] ?? [];

    // Validate inputs
    if (empty($employee_id)) {
        $error = "Please select an employee";
    } elseif (empty($products_stock)) {
        $error = "Please add at least one product";
    } else {
        mysqli_begin_transaction($conn);

        try {
            // Insert into Stock table
            $sql = "INSERT INTO Stock (Date, EmployeeID) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $date, $employee_id);
            $stmt->execute();

            $stock_id = $conn->insert_id;

            // Insert each product into StockProduct table
            foreach ($products_stock as $product_id => $quantity) {
                if ($quantity > 0) {
                    $sql = "INSERT INTO StockProduct (StockID, ProductID, Quantity) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iii", $stock_id, $product_id, $quantity);
                    $stmt->execute();
                }
            }

            mysqli_commit($conn);
            header('Location: stock.php?success=Stock entry added successfully');
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stock</title>
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
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add New Stock Entry</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control"
                                    value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Employee</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    <?php while ($employee = mysqli_fetch_assoc($employees)): ?>
                                        <option value="<?php echo $employee['EmployeeId']; ?>">
                                            <?php echo htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Products</label>
                                <div class="input-group mb-3">
                                    <input type="text" id="product_search" class="form-control"
                                        placeholder="Search products..." autocomplete="off">
                                    <button type="button" class="btn btn-primary" id="add_product">Add Product</button>
                                </div>
                                <div id="product_search_results" class="dropdown-menu w-100"></div>

                                <div class="selected-products mt-3">
                                    <table class="table" id="selected_products_table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Selected products will be added here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Stock Entry</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Stock History</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($stock = mysqli_fetch_assoc($stocks)): ?>
                                    <tr>
                                        <td><?php echo $stock['Date']; ?></td>
                                        <td><?php echo htmlspecialchars($stock['EmployeeName'] . ' ' . $stock['EmployeeLastName']); ?></td>
                                        <td><?php echo htmlspecialchars($stock['Products']); ?></td>
                                        <td>
                                            <a href="view_stock.php?id=<?php echo $stock['StockID']; ?>"
                                                class="btn btn-info btn-sm">View</a>
                                            <a href="delete_stock.php?id=<?php echo $stock['StockID']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this stock entry?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

                fetch(`stock.php?search_product=${encodeURIComponent(search)}`)
                    .then(response => response.json())
                    .then(data => {
                        productResults.innerHTML = '';
                        data.forEach(product => {
                            const div = document.createElement('div');
                            div.className = 'dropdown-item';
                            div.textContent = product.ProductName;
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

                // Check if product already exists
                const existingRows = selectedProductsTable.getElementsByTagName('tr');
                for (let row of existingRows) {
                    if (row.dataset.productId === selectedProduct.ProductID) {
                        alert('This product is already added!');
                        return;
                    }
                }

                // Add new row
                const row = selectedProductsTable.insertRow();
                row.dataset.productId = selectedProduct.ProductID;
                row.innerHTML = `
                <td>${selectedProduct.ProductName}</td>
                <td>
                    <input type="number" name="products[${selectedProduct.ProductID}]" 
                           class="form-control" value="1" min="1" style="width: 100px">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-product">Remove</button>
                </td>
            `;

                // Add remove button handler
                row.querySelector('.remove-product').addEventListener('click', function() {
                    row.remove();
                });

                // Clear selection
                selectedProduct = null;
                productSearch.value = '';
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!productSearch.contains(e.target) && !productResults.contains(e.target)) {
                    productResults.classList.remove('show');
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>