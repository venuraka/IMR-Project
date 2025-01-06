<?php
include('dbcon.php');
$fname = "";
$lname = "";
$contact = "";
$address = "";
$email = "";
$successMessage = "";
$errorMessage = "";
// setting the initial value of id
$newId = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

 //use this function for auto increment of ID if neded

  $query = "SELECT MAX(CustomerID) AS maxId FROM customer";
    $result = mysqli_query($conn, $query);
    $newId = 1;

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $newId = $row['maxId'] + 1;
    }

    $fname = $_POST['firstName'];
    $lname = $_POST['lastName'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $email = $_POST['email'];

    // Check if the email already exists
    $checkEmailQuery = "SELECT * FROM customer WHERE Email = '$email'";
    $checkEmailResult = mysqli_query($conn, $checkEmailQuery);

    if (mysqli_num_rows($checkEmailResult) > 0) {
        $errorMessage = "The email '$email' is already registered!";
    } else {
        // Insert the new record
        $sql = "INSERT INTO customer ( CustomerID,FirstName, LastName, Contact, Address, Email) 
                VALUES ('$newId','$fname', '$lname', '$contact', '$address', '$email')";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            $errorMessage = "Error: " . $sql . "<br>" . mysqli_error($conn);
        } else {
            $successMessage = "Customer data has been added successfully!";
            header("Location: customer.php");
            exit();
        }
    }
    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <title>Document</title>
</head>
<body>
  <!-- nav bar start-->
  <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">POS System</a>
            <div class="ms-auto">
                <a href="customer.php" class="btn btn-outline-primary">Back</a>
            </div>
        </div>
    </nav>
    <!-- nav bar end-->
    <div class="container mt-4">
    <?php if (!empty($successMessage)): ?>
    <div class="alert alert-success" role="alert">
      <?php echo $successMessage; ?>
    </div>
  <?php endif; ?>
    <h2 class="mb-4">Add Customer</h2>
    <form method="post">
      <!-- use this function for show ID if neded -->
    
      <div class="mb-3">
        <label for="firstName" class="form-label">First Name</label>
        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter First Name" value="<?php echo $fname?>" required>
      </div>
      <div class="mb-3">
        <label for="lastName" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter Last Name" value="<?php echo $lname?>" required>
      </div>
      <div class="mb-3">
        <label for="contact" class="form-label">Contact</label>
        <input type="text" class="form-control" id="contact" name="contact" placeholder="Enter Contact Number" value="<?php echo $contact?>" required>
      </div>
      <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea class="form-control" id="address" name="address" placeholder="Enter Address" rows="3" value="<?php echo $address?>" required ></textarea>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" value="<?php echo $email?>" required>
      </div>
      <button type="submit" class="btn btn-primary">Add</button>
      <button type="reset" class="btn btn-secondary" onclick="window.location.href='customer.php'">Cancel</button>
    </form>
  </div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>