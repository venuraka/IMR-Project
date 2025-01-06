<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM Supplier WHERE SupplierID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $supplier = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];

    $sql = "UPDATE Supplier SET SupplierName = ?, Address = ?, Contact = ?, Email = ? WHERE SupplierID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $address, $contact, $email, $id);
    $stmt->execute();
    header('Location: supplier.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
            <div class="ms-auto">
                <a href="supplier.php" class="btn btn-outline-primary">Back to Suppliers</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Update Supplier</h5>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $supplier['SupplierID']; ?>">
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Supplier Name"
                            value="<?php echo $supplier['SupplierName']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="address" class="form-control" placeholder="Address"
                            value="<?php echo $supplier['Address']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="contact" class="form-control" placeholder="Contact"
                            value="<?php echo $supplier['Contact']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email"
                            value="<?php echo $supplier['Email']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>