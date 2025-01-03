<?php
include ('dbcon.php');
$query = "SELECT * FROM `Customer`";
$query_run = mysqli_query($conn, $query);
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
  <nav class="navbar navbar-expand-lg navbar">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Navbar</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Features</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Pricing</a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" aria-disabled="true">Disabled</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
    <!-- nav bar end-->

    <!-- Display table start  -->
     <div class="mt-2 mx-4 maincard">
      <div class="row">
          <div class="card maincard">
            <div class="card-header">
              <h2>Display Table</h2>
              </div>
              <div class="card-body">
                <div class="p-2">
                <button type="button" class="btn btn-primary" onclick="window.location.href='addcustomer.php'">ADD Data</button>
              </div>
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Customer ID</th>
                      <th>First Name</th>
                      <th>Last Name</th>
                      <th>Contact</th>
                      <th>Address</th>
                      <th>Email</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <?php 
                      while ($row = mysqli_fetch_assoc($query_run))
                      {
                      ?>
                          <td><?php  echo $row['CustomerID']; ?></td>
                          <td><?php  echo $row['FirstName']; ?></td>
                          <td><?php  echo $row['LastName']; ?></td>
                          <td><?php  echo $row['Contact']; ?></td>
                          <td><?php  echo $row['Address']; ?></td>
                          <td><?php  echo $row['Email']; ?></td>
                          <td> <button type="button" class="btn btn-success" href="deletecustomer.php?id=$row['CustomerID']">Edit Data</button>     
                         <button type="button" class="btn btn-danger" href="updatecustomer.php?id=$row['CustomerID']">Delete Data</button>     </td>
                      </tr>
                      <?php
                      }
                      ?>
                  </tbody>
                </table>
              </div>
            </div>
        </div>
      </div>
     </div>
     <!-- Display table end -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>