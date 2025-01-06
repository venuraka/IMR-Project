<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "
        SELECT c.*, rc.AverageSpending, rc.VisitFrequency 
        FROM RegularCustomer rc
        JOIN Customer c ON rc.CustomerID = c.CustomerID
        WHERE rc.CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $avg_spending = $_POST['average_spending'];
    $visit_frequency = $_POST['visit_frequency'];

    $sql = "UPDATE RegularCustomer SET AverageSpending = ?, VisitFrequency = ? WHERE CustomerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dsi", $avg_spending, $visit_frequency, $id);

    if ($stmt->execute()) {
        header('Location: regular_customer.php?success=Regular customer updated successfully');
    } else {
        header('Location: regular_customer.php?error=Failed to update regular customer');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Regular Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
            <div class="ms-auto">
                <a href="regular_customer.php" class="btn btn-outline-primary">Back to Regular Customers</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Update Regular Customer</h5>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $customer['CustomerID']; ?>">
                            <div class="mb-3">
                                <label>Customer Name</label>
                                <input type="text" class="form-control" value="<?php echo $customer['FirstName'] . ' ' . $customer['LastName']; ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Average Spending</label>
                                <input type="number" step="0.01" name="average_spending" class="form-control"
                                    value="<?php echo $customer['AverageSpending']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Visit Frequency</label>
                                <select name="visit_frequency" class="form-control" required>
                                    <option value="Daily" <?php if ($customer['VisitFrequency'] == 'Daily') echo 'selected'; ?>>Daily</option>
                                    <option value="Weekly" <?php if ($customer['VisitFrequency'] == 'Weekly') echo 'selected'; ?>>Weekly</option>
                                    <option value="Monthly" <?php if ($customer['VisitFrequency'] == 'Monthly') echo 'selected'; ?>>Monthly</option>
                                    <option value="Quarterly" <?php if ($customer['VisitFrequency'] == 'Quarterly') echo 'selected'; ?>>Quarterly</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Regular Customer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>