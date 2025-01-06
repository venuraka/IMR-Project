<?php
include('dbcon.php');

// Get suppliers and categories for dropdowns
$suppliers = mysqli_query($conn, "SELECT * FROM Supplier");
$categories = mysqli_query($conn, "SELECT * FROM Category");

// Get all products with supplier and category names
$products = mysqli_query($conn, "
    SELECT p.*, s.SupplierName, c.Name as CategoryName 
    FROM Product p
    LEFT JOIN Supplier s ON p.SupplierID = s.SupplierID
    LEFT JOIN Category c ON p.CategoryID = c.CategoryID
");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $cost_price = $_POST['cost_price'];
    $supplier_id = $_POST['supplier_id'];
    $category_id = $_POST['category_id'];

    $sql = "INSERT INTO Product (ProductName, Price, CostPrice, SupplierID, CategoryID) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddii", $name, $price, $cost_price, $supplier_id, $category_id);
    $stmt->execute();
    header('Location: product.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
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
                        <h5 class="card-title">Add New Product</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <input type="text" name="name" class="form-control" placeholder="Product Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="Selling Price" required>
                            </div>
                            <div class="mb-3">
                                <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="Cost Price" required>
                            </div>
                            <div class="mb-3">
                                <select name="supplier_id" class="form-control" required>
                                    <option value="">Select Supplier</option>
                                    <?php while ($supplier = mysqli_fetch_assoc($suppliers)): ?>
                                        <option value="<?php echo $supplier['SupplierID']; ?>">
                                            <?php echo htmlspecialchars($supplier['SupplierName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $category['CategoryID']; ?>">
                                            <?php echo htmlspecialchars($category['Name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body ">
                        <h5 class="card-title">Products List</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Cost Price</th>
                                    <th>Supplier</th>
                                    <th>Category</th>
                                    <th>Actions</th>
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
                                        <td>
                                            <a href="update_product.php?id=<?php echo $row['ProductID']; ?>"
                                                class="btn btn-primary btn-sm">Edit</a>
                                            <a href="delete_product.php?id=<?php echo $row['ProductID']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
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
</body>

</html>