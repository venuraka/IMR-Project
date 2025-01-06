<?php
include('dbcon.php');
// Query to get loyalty points data
$sql = "SELECT 
            CASE 
                WHEN l.LoyaltyPoints >= 1 THEN 'Gold'
                WHEN l.LoyaltyPoints >= 2 THEN 'Silver'
                ELSE 'Bronze'
            END AS membership_level,
            COUNT(*) as member_count,
            AVG(l.LoyaltyPoints) as avg_points
        FROM LoyaltyCustomer l
        GROUP BY 
            CASE 
                WHEN l.LoyaltyPoints >= 1 THEN 'Gold'
                WHEN l.LoyaltyPoints >= 2 THEN 'Silver'
                ELSE 'Bronze'
            END";

$result = $conn->query($sql);

// Initialize arrays to store data
$labels = [];
$memberCounts = [];
$avgPoints = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $labels[] = $row["membership_level"];
        $memberCounts[] = $row["member_count"];
        $avgPoints[] = round($row["avg_points"], 2);
    }
} 


// Convert data to JSON for JavaScript
$labelsJSON = json_encode($labels);
$memberCountsJSON = json_encode($memberCounts);
$avgPointsJSON = json_encode($avgPoints);
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
                    <li class="nav-item"><a class="nav-link text-white" href="#overview">Overview</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#sales">Sales</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#inventory">Inventory</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="customer.php">Customers</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#reports">Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#settings">Settings</a></li>
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
                            <h3 class="card-title">Today's Transactions</h3>
                            <p class="card-text fs-4">
                            <?php 
                                            $sql = "SELECT SUM(TotalAmount) AS totalSum FROM Sale";
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
            <div style="width: 80%; margin: auto; " class="p-5" >
        <canvas id="loyaltyChart"></canvas>
             </div>

            <!-- Inventory Section -->
            <section id="inventory" class="my-5">
                <h2>Inventory Status</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Product A</td>
                            <td>100</td>
                            <td>$10.00</td>
                        </tr>
                        <tr>
                            <td>Product B</td>
                            <td>50</td>
                            <td>$15.00</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Customers Section -->
            <section id="customers" class="my-5">
                <h2>Customer List</h2>
                <ul class="list-group">
                    <li class="list-group-item">Customer A</li>
                    <li class="list-group-item">Customer B</li>
                    <li class="list-group-item">Customer C</li>
                </ul>
            </section>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
          // Create the chart using the PHP data
    const ctx = document.getElementById('loyaltyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo $labelsJSON; ?>,
            datasets: [
                {
                    label: 'Number of Members',
                    data: <?php echo $memberCountsJSON; ?>,
                    backgroundColor: [
                        'rgba(255, 215, 0, 0.6)',  // Gold
                        'rgba(192, 192, 192, 0.6)', // Silver
                        'rgba(205, 127, 50, 0.6)'   // Bronze
                    ],
                    borderColor: [
                        'rgba(255, 215, 0, 1)',
                        'rgba(192, 192, 192, 1)',
                        'rgba(205, 127, 50, 1)'
                    ],
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Average Points',
                    data: <?php echo $avgPointsJSON; ?>,
                    type: 'line',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: false,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Number of Members'
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Average Points'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Loyalty Program Distribution',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });
    </script>
</body>
</html>
