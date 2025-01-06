<?php
include('dbcon.php');
$query = "SELECT * FROM Discount";
$suppliers = mysqli_query($conn, $query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ProductID = $_POST['ProductID'];
    $name = $_POST['name'];  
    $percentage = $_POST['percentage'];  
    $valiedfrom = $_POST['valiedfrom'];  
    $valiedto = $_POST['valiedto'];  

    $stmt = $conn->prepare("INSERT INTO Discount (ProductID, DiscountName, DiscountPercentage, ValidFrom, ValidTo) VALUES (?, ?, ?, ?, ?)");  // added VALUES clause
    $stmt->bind_param("sssss", $ProductID, $name, $percentage, $valiedfrom, $valiedto);

    if ($stmt->execute()) {
        header('Location: discount.php?success=Discount added successfully');
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
    <title>Manage Suppliers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg" >
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-primary">Back to Home</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php
        if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add New Discount</h5>
                        <form method="POST">
    <div class="mb-3">
        <input type="text" name="ProductID" class="form-control" placeholder="Product ID" required>
    </div>
    <div class="mb-3">
        <input type="text" name="name" class="form-control" placeholder="Discount Name" required>
    </div>
    <div class="mb-3">
        <input type="text" name="percentage" class="form-control" placeholder="Discount Percentage" required>
    </div>
    <div class="mb-3">
        <input type="date" name="valiedfrom" class="form-control" placeholder="Valid From" required>
    </div>
    <div class="mb-3">
        <input type="date" name="valiedto" class="form-control" placeholder="Valid To" required>
    </div>
    <button type="submit" class="btn btn-primary">Add Discount</button>
</form>

                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Discount List</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>percentage</th>
                                    <th>Valied From</th>
                                    <th>Valied To</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($suppliers)): ?>
                                    <tr>
                                    <td><?php echo $row['ProductID']; ?></td>
<td><?php echo $row['DiscountName']; ?></td>
<td><?php echo $row['DiscountPercentage']; ?></td>
<td><?php echo $row['ValidFrom']; ?></td>
<td><?php echo $row['ValidTo']; ?></td>
                                        <td>
                                        <a href="update_discount.php?id=<?php echo $row['ProductID']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="delete_discount.php?id=<?php echo $row['ProductID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this discount?')">Delete</a>

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
    </div>
</body>

</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>