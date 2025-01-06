<?php
include('dbcon.php');

// Get customers with sales who are not regular customers yet
$available_customers = mysqli_query($conn, "
    SELECT DISTINCT c.*, 
           COUNT(s.SaleID) as TotalSales,
           AVG(s.TotalAmount) as AvgSpending,
           DATEDIFF(MAX(s.SaleDate), MIN(s.SaleDate)) as DateRange
    FROM Customer c
    JOIN Sale s ON c.CustomerID = s.CustomerID
    LEFT JOIN RegularCustomer rc ON c.CustomerID = rc.CustomerID
    WHERE rc.CustomerID IS NULL
    GROUP BY c.CustomerID
");

// Get all regular customers with their details
$regular_customers = mysqli_query($conn, "
    SELECT c.*, rc.AverageSpending, rc.VisitFrequency,
           COUNT(s.SaleID) as TotalSales,
           MIN(s.SaleDate) as FirstVisit,
           MAX(s.SaleDate) as LastVisit
    FROM RegularCustomer rc
    JOIN Customer c ON rc.CustomerID = c.CustomerID
    LEFT JOIN Sale s ON c.CustomerID = s.CustomerID
    GROUP BY c.CustomerID, rc.AverageSpending, rc.VisitFrequency
");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['customer_id'])) {
        $customer_id = $_POST['customer_id'];

        // Call the stored procedure directly
        $stmt = $conn->prepare("CALL CalculateCustomerStats(?)");
        $stmt->bind_param("i", $customer_id);

        if ($stmt->execute()) {
            header('Location: regular_customer.php?success=Regular customer added successfully');
        } else {
            header('Location: regular_customer.php?error=Failed to add regular customer');
        }
        exit();
    }
}

// Function to calculate time since last visit
function getTimeSinceLastVisit($lastVisit)
{
    if (!$lastVisit) return "No visits";
    $last = new DateTime($lastVisit);
    $now = new DateTime();
    $diff = $now->diff($last);

    if ($diff->y > 0) return $diff->y . " year(s) ago";
    if ($diff->m > 0) return $diff->m . " month(s) ago";
    if ($diff->d > 0) return $diff->d . " day(s) ago";
    return "Today";
}

function calculateVisitFrequency($totalSales, $dateRange)
{
    if ($totalSales == 0) return 'New Customer';
    if ($dateRange == 0) return 'First Visit';

    $ratio = $totalSales / max($dateRange, 1);

    if ($ratio >= 0.14) return 'Daily';
    if ($ratio >= 0.07) return 'Weekly';
    if ($ratio >= 0.03) return 'Monthly';
    return 'Quarterly';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Regular Customers</title>
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
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add New Regular Customer</h5>
                        <form method="POST" id="regularCustomerForm">
                            <div class="mb-3">
                                <label>Select Customer</label>
                                <select name="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    <?php while ($customer = mysqli_fetch_assoc($available_customers)):
                                        $visitFreq = calculateVisitFrequency($customer['TotalSales'], $customer['DateRange']);
                                    ?>
                                        <option value="<?php echo $customer['CustomerID']; ?>"
                                            data-avg-spending="<?php echo number_format($customer['AvgSpending'], 2); ?>"
                                            data-visit-freq="<?php echo $visitFreq; ?>">
                                            <?php echo htmlspecialchars($customer['FirstName'] . ' ' . $customer['LastName']); ?>
                                            (Avg: $<?php echo number_format($customer['AvgSpending'], 2); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Average Spending</label>
                                <input type="text" id="average_spending_display" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Visit Frequency</label>
                                <input type="text" id="visit_frequency_display" class="form-control" readonly>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Regular Customer</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Regular Customers List</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Contact</th>
                                    <th>Average Spending</th>
                                    <th>Visit Frequency</th>
                                    <th>Total Sales</th>
                                    <th>Last Visit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($customer = mysqli_fetch_assoc($regular_customers)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($customer['FirstName'] . ' ' . $customer['LastName']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['Contact']); ?></td>
                                        <td>$<?php echo number_format($customer['AverageSpending'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($customer['VisitFrequency']); ?></td>
                                        <td><?php echo $customer['TotalSales']; ?></td>
                                        <td><?php echo getTimeSinceLastVisit($customer['LastVisit']); ?></td>
                                        <td>
                                            <a href="update_regular_customer.php?id=<?php echo $customer['CustomerID']; ?>"
                                                class="btn btn-primary btn-sm">Edit</a>
                                            <a href="delete_regular_customer.php?id=<?php echo $customer['CustomerID']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to remove this regular customer status?')">Remove</a>
                                            <button type="button"
                                                onclick="recalculateStats(<?php echo $customer['CustomerID']; ?>)"
                                                class="btn btn-info btn-sm">Recalculate</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const customerSelect = document.querySelector('select[name="customer_id"]');
            const avgSpendingDisplay = document.getElementById('average_spending_display');
            const visitFreqDisplay = document.getElementById('visit_frequency_display');

            customerSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const avgSpending = selectedOption.getAttribute('data-avg-spending');
                const visitFreq = selectedOption.getAttribute('data-visit-freq');

                avgSpendingDisplay.value = avgSpending ? '$' + avgSpending : '';
                visitFreqDisplay.value = visitFreq || '';
            });
        });

        function recalculateStats(customerId) {
            const form = document.createElement('form');
            form.method = 'POST';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'customer_id';
            input.value = customerId;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>