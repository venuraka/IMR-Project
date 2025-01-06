<?php
include('dbcon.php');
$products = mysqli_query($conn, "
    SELECT p.*, s.SupplierName, c.Name as CategoryName 
    FROM Product p
    LEFT JOIN Supplier s ON p.SupplierID = s.SupplierID
    LEFT JOIN Category c ON p.CategoryID = c.CategoryID
");


// Query to get top customers based on loyalty points
$sql = "SELECT CustomerID, LoyaltyPoints FROM Loyalty ORDER BY LoyaltyPoints DESC";
$result = $conn->query($sql);

// Initialize arrays for data
$customerIDs = [];
$loyaltyPoints = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customerIDs[] = "Customer " . $row['CustomerID']; // Adding "Customer" prefix for better labeling
        $loyaltyPoints[] = $row['LoyaltyPoints'];
    }
}

// Convert data to JSON for JavaScript
$customerIDsJSON = json_encode($customerIDs);
$loyaltyPointsJSON = json_encode($loyaltyPoints);

$query = "SELECT * FROM Employee";
$employees = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="script.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <aside class="bg-dark text-white p-3" style="width: 250px;">
            <h2 class="text-center">POS Dashboard</h2>
            <nav>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white" href="supplier.php">Suppliers</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="category.php">Categories</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="product.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="employee.php">Employees</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="customer.php">Customers</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="regular_customer.php">Regular Customers</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="stock.php">Stock</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="discount.php">Discount</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="sales.php">New Sale</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="sale_report.php">Sales Report</a></li>

                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow-1 p-4">
            <header class="d-flex justify-content-between align-items-center mb-4">
                <h1>Dashboard</h1>
           
            </header>

            <!-- Overview Section -->
            <section id="overview" class="row g-3">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="card-title">Total Customers</h3>
                            <p class="card-text fs-4">       
                                    <?php 
                                            $sql = "SELECT COUNT(*) FROM `Customer`";
                                            $result = mysqli_query($conn, $sql);
                                            $count = mysqli_fetch_row($result)[0];

                                            // Display the number of records
                                            echo $count;
                                      ?>
                             </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="card-title">Total Cashiers</h3>
                            <p class="card-text fs-4">
                            <?php 
                                            $sql = "SELECT COUNT(*) FROM `Cashier`";
                                            $result = mysqli_query($conn, $sql);
                                            $count = mysqli_fetch_row($result)[0];

                                            // Display the number of records
                                            echo $count;
                                      ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="card-title">Total Items on stock</h3>
                            <p class="card-text fs-4">
                                <?php
                                $sql = "SELECT COUNT(*) FROM `Product`";
                                $result = mysqli_query($conn, $sql);
                                $count = mysqli_fetch_row($result)[0];
                                // Display the number of records
                                echo $count;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="card-title">Total Loyalty Customers</h3>
                            <p class="card-text fs-4">
                            <?php 
                                            $sql = "SELECT COUNT(*) FROM `Loyalty`";
                                            $result = mysqli_query($conn, $sql);
                                            $count = mysqli_fetch_row($result)[0];

                                            // Display the number of records
                                            echo $count;
                                      ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="card-title">Avalable Discounts</h3>
                            <p class="card-text fs-4">
                            <?php 
                                            $sql = "SELECT COUNT(*) FROM `Discount`";
                                            $result = mysqli_query($conn, $sql);
                                            $count = mysqli_fetch_row($result)[0];

                                            // Display the number of records
                                            echo $count;
                                      ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="card-title">Total Employes</h3>
                            <p class="card-text fs-4">
                            <?php 
                                            $sql = "SELECT COUNT(*) FROM `Employee`";
                                            $result = mysqli_query($conn, $sql);
                                            $count = mysqli_fetch_row($result)[0];

                                            // Display the number of records
                                            echo $count;
                                      ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="card-title">Number Of Transaction Today</h3>
                            <p class="card-text fs-4">
                            <?php 
                                            $sql = "SELECT COUNT(*) FROM `Payment`";
                                            $result = mysqli_query($conn, $sql);
                                            $count = mysqli_fetch_row($result)[0];

                                            // Display the number of records
                                            echo $count;
                                      ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="card-title">Number Of Suppliyers</h3>
                            <p class="card-text fs-4">
                            <?php 
                                            $sql = "SELECT COUNT(*) FROM `Supplier`";
                                            $result = mysqli_query($conn, $sql);
                                            $count = mysqli_fetch_row($result)[0];

                                            // Display the number of records
                                            echo $count;
                                      ?>
                            </p>
                        </div>
                    </div>
                </div>

            </section>

            <!-- Sales Section -->
            <div style="width: 80%; margin: auto; padding: 20px;">
        <canvas id="loyaltyBarChart"></canvas>
    </div>

            <!-- Inventory Section -->
            <section id="inventory" class="my-5">
                <h2>Inventory Status</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                                  <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Cost Price</th>
                                    <th>Supplier</th>
                                    <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($products)): ?>
                                    <tr>
                                        <td><?php echo $row['ProductID']; ?></td>
                                        <td><?php echo $row['ProductName']; ?></td>
                                        <td><?php echo $row['Price']; ?></td>
                                        <td><?php echo $row['CostPrice']; ?></td>
                                        <td><?php echo $row['SupplierName']; ?></td>
                                        <td><?php echo $row['CategoryName']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <!-- Customers Section -->
            <section id="customers" class="my-5">
                <h2>Employee List</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                                  <th>ID</th>
                                    <th>Name</th>
                                    <th>DOB</th>
                                    <th>Join Date</th>
                                    <th>Contact</th>
                                    <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($employees)): ?>
                                    <tr>
                                        <td><?php echo $row['EmployeeId']; ?></td>
                                        <td><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></td>
                                        <td><?php echo $row['DOB']; ?></td>
                                        <td><?php echo $row['JoinDate']; ?></td>
                                        <td><?php echo $row['Contact']; ?></td>
                                        <td><?php echo $row['Email']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data from PHP
        const customerIDs = <?php echo $customerIDsJSON; ?>;
        const loyaltyPoints = <?php echo $loyaltyPointsJSON; ?>;

        // Create the bar chart
        const ctx = document.getElementById('loyaltyBarChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: customerIDs,
                datasets: [{
                    label: 'Loyalty Points',
                    data: loyaltyPoints,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Top Customers by Loyalty Points'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Loyalty Points'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Customers'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
