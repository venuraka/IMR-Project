<?php
include('dbcon.php');

// Get suppliers and categories for dropdowns
$suppliers = mysqli_query($conn, "SELECT * FROM Supplier");
$categories = mysqli_query($conn, "SELECT * FROM Category");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM Product WHERE ProductID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $cost_price = $_POST['cost_price'];
    $supplier_id = $_POST['supplier_id'];
    $category_id = $_POST['category_id'];

    $sql = "UPDATE Product SET ProductName=?, Price=?, CostPrice=?, SupplierID=?, CategoryID=? WHERE ProductID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddiii", $name, $price, $cost_price, $supplier_id, $category_id, $id);

    if ($stmt->execute()) {
        header('Location: product.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
            <div class="ms-auto">
                <a href="product.php" class="btn btn-outline-primary">Back to Products</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Update Product</h5>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $product['ProductID']; ?>">
                            <div class="mb-3">
                                <label>Product Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $product['ProductName']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Price</label>
                                <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['Price']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Cost Price</label>
                                <input type="number" step="0.01" name="cost_price" class="form-control" value="<?php echo $product['CostPrice']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Supplier</label>
                                <select name="supplier_id" class="form-control" required>
                                    <?php while ($supplier = mysqli_fetch_assoc($suppliers)): ?>
                                        <option value="<?php echo $supplier['SupplierID']; ?>"
                                            <?php echo ($supplier['SupplierID'] == $product['SupplierID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($supplier['SupplierName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $category['CategoryID']; ?>"
                                            <?php echo ($category['CategoryID'] == $product['CategoryID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['Name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>