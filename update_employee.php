<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM Employee WHERE EmployeeId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    $dob = $_POST['dob'];
    $joindate = $_POST['joindate'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];

    $sql = "UPDATE Employee SET FirstName=?, LastName=?, DOB=?, JoinDate=?, Address=?, Contact=?, Email=? WHERE EmployeeId=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $fname, $lname, $dob, $joindate, $address, $contact, $email, $id);

    if ($stmt->execute()) {
        header('Location: employee.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
            <div class="ms-auto">
                <a href="employee.php" class="btn btn-outline-primary">Back to Employees</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Update Employee</h5>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $employee['EmployeeId']; ?>">
                            <div class="mb-3">
                                <label>First Name</label>
                                <input type="text" name="firstname" class="form-control" value="<?php echo $employee['FirstName']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Last Name</label>
                                <input type="text" name="lastname" class="form-control" value="<?php echo $employee['LastName']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?php echo $employee['DOB']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Join Date</label>
                                <input type="date" name="joindate" class="form-control" value="<?php echo $employee['JoinDate']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control" value="<?php echo $employee['Address']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Contact</label>
                                <input type="text" name="contact" class="form-control" value="<?php echo $employee['Contact']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo $employee['Email']; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Employee</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>