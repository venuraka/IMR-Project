<?php
include('dbcon.php');
$query = "SELECT * FROM Employee";
$employees = mysqli_query($conn, $query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    $dob = $_POST['dob'];
    $joindate = $_POST['joindate'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];

    $sql = "INSERT INTO Employee (FirstName, LastName, DOB, JoinDate, Address, Contact, Email) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $fname, $lname, $dob, $joindate, $address, $contact, $email);
    $stmt->execute();
    header('Location: employee.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees</title>
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
                        <h5 class="card-title">Add New Employee</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <input type="text" name="firstname" class="form-control" placeholder="First Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="lastname" class="form-control" placeholder="Last Name" required>
                            </div>
                            <div class="mb-3">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Join Date</label>
                                <input type="date" name="joindate" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="address" class="form-control" placeholder="Address" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="contact" class="form-control" placeholder="Contact" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Employee</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Employees List</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>DOB</th>
                                    <th>Join Date</th>
                                    <th>Contact</th>
                                    <th>Email</th>
                                    <th>Actions</th>
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
                                        <td>
                                            <a href="update_employee.php?id=<?php echo $row['EmployeeId']; ?>"
                                                class="btn btn-primary btn-sm">Edit</a>
                                            <a href="delete_employee.php?id=<?php echo $row['EmployeeId']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
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