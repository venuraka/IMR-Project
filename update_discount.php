<?php
include('dbcon.php');

// Fetch discount details if ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM Discount WHERE ProductID = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $discount = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ProductID = $_POST['ProductID'];
    $name = $_POST['Name'];
    $percentage = $_POST['Percentage'];
    $validFrom = $_POST['ValidFrom'];
    $validTo = $_POST['ValidTo'];

    $stmt = $conn->prepare("UPDATE Discount SET DiscountName = ?, DiscountPercentage = ?, ValidFrom = ?, ValidTo = ? WHERE ProductID = ?");
    $stmt->bind_param("sssss", $name, $percentage, $validFrom, $validTo, $ProductID);

    if ($stmt->execute()) {
        header('Location: discount.php?success=Discount updated successfully');
    } else {
        header('Location: discount.php?error=' . $conn->error);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Discount</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
            <div class="ms-auto">
                <a href="discount.php" class="btn btn-outline-primary">Back to Discounts</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Update Discount</h5>
                        <?php if (isset($discount)): ?>
                            <form method="POST">
                                <input type="hidden" name="ProductID" value="<?php echo $discount['ProductID']; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Discount Name</label>
                                    <input type="text" name="Name" class="form-control" 
                                           value="<?php echo $discount['DiscountName']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Discount Percentage</label>
                                    <input type="text" name="Percentage" class="form-control" 
                                           value="<?php echo $discount['DiscountPercentage']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Valid From</label>
                                    <input type="date" name="ValidFrom" class="form-control" 
                                           value="<?php echo $discount['ValidFrom']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Valid To</label>
                                    <input type="date" name="ValidTo" class="form-control" 
                                           value="<?php echo $discount['ValidTo']; ?>" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Discount</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-danger">Discount not found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>