<?php session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 ?><!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin Page</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/style.css">  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/5.5.2/css/ionicons.min.css">
  <link rel="stylesheet" href="admin_product.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <style>
    /* General Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f3f7;
      color: #333;
      line-height: 1.6;
    }

    /* Sidebar Styling */
    .sidenav {
      background-color: #2c3e50;
      color: white;
      height: 100%;
      width: 250px;
      position: fixed;
      padding: 20px;
      top: 0;
      left: 0;
      z-index: 1000;
      transition: width 0.3s ease;
    }

    .sidenav .logo h2 {
      margin: 0;
      font-size: 24px;
      color: #ecf0f1;
      margin-bottom: 30px;
    }

    .sidenav ul {
      list-style: none;
      padding: 0;
    }

    .sidenav ul li {
      margin: 20px 0;
      transition: background-color 0.3s;
    }

    .sidenav ul li a {
      text-decoration: none;
      color: #ecf0f1;
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 18px;
      padding: 10px 20px;
      border-radius: 5px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .sidenav ul li a:hover {
      background-color: #3498db;
      color: #ecf0f1;
    }

    /* Main content styling */
    .main {
      margin-left: 270px;
      padding: 30px;
      transition: margin-left 0.3s ease;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      background-color: #fff;
      padding: 10px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .toggle ion-icon {
      font-size: 30px;
      color: #3498db;
      cursor: pointer;
    }

    .card-header h2 {
      font-size: 24px;
      color: #3498db;
      margin-bottom: 20px;
    }

    /* Table Styling */
    table {
      width: 100%;
      border-collapse: collapse;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
    }

    th, td {
      padding: 12px 20px;
      text-align: center;
      border-bottom: 2px solid #ddd;
    }

    th {
      background-color: #3498db;
      color: white;
    }

    td {
      color: #555;
    }

    tr:hover {
      background-color: #f0f8ff;
    }

    /* Action button styling */
    td a {
      text-decoration: none;
      color: #3498db;
      font-size: 18px;
      transition: color 0.3s ease;
    }

    td a:hover {
      color: #2980b9;
    }

    /* Responsive Design */
    @media screen and (max-width: 767px) {
      .sidenav {
        width: 200px;
      }

      .main {
        margin-left: 220px;
      }

      .topbar {
        flex-direction: column;
      }
    }
/* Make the sidebar taller and adjust its layout */
.sidebar {
    height: 100%; /* Make it take full height */
    min-height: 100vh; /* Ensure it's at least as tall as the viewport */
    position: fixed; /* Keep it fixed on the left side */
    left: 0;
    top: 0;
    width: 205px; /* Match your current sidebar width */
    background-color: #1e272e; /* Maintain your dark theme */
    overflow-y: auto; /* Enable scrolling if needed */
}

/* Adjust the main content area to accommodate the sidebar */
.main-content {
    margin-left: 205px; /* Match the sidebar width */
    padding-left: 20px; /* Add some space between sidebar and content */
    width: calc(100% - 205px); /* Ensure proper width calculation */
}

/* Ensure the dashboard content fits properly */
.dashboard {
    padding: 20px;
    width: 100%;
    box-sizing: border-box;
}
a {
    text-decoration: none;
    color: inherit; /* Optional: keeps the same color as surrounding text */
}

  </style>
</head>

<body>
  <<!-- Sidebar -->
  <nav class="sidebar">
            <div class="logo">
                <h2>Admin Panel</h2>
            </div>
            <ul class="nav-links">
            <li ><a href="admin_product.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="admin_order_list.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="admindetails.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                <li><a href="custdetails.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</li>
</ul>
        </nav>
        <main class="main-content">
            <header class="top-bar">
                <div class="search">
                    <input type="text" id="productSearch" placeholder="Search products...">
                </div>
                <div class="user-info">
    <span>Admin User</span>
    <i class="fas fa-user-circle" style="font-size: 24px; color: #333;"></i>
</div>

            </header>
  

    <!-- Customer Details Table -->
    <div class="recentOrders">
      <div class="card-header">
      <div class="dashboard">
      <h1>Customer Management</h1>
      </div>
      <div class="card-body">
        <table class="table table-hover text-center">
          <thead>
            <tr>
              <th>Customer ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone Number</th>
              <th>Date of Birth</th>
              <th>Address</th>
              <th>Gender</th>
              <th>Registration Time</th>
              
            </tr>
          </thead>
<tbody>
  <?php
    $header = array(
        "customerID" => "Customer ID",
        "name" => "Name",
        "email" => "Email",
        "phonenum" => "Phone Number",
        "dateofbirth" => "Date of Birth",
        "address" => "Address",
        "gender" => "Gender",
        "registrationtime" => "Registration Time",
        
    );

  // Database connection
$host = 'localhost';
$dbname = 'graduation_store';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
    $sql = "SELECT customerID, name, email, phonenum, dateofbirth, address, gender, registrationtime FROM customer;";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_object()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row->customerID) . "</td>"; 
            echo "<td>" . htmlspecialchars($row->name) . "</td>"; 
            echo "<td>" . htmlspecialchars($row->email) . "</td>"; 
            echo "<td>" . htmlspecialchars($row->phonenum) . "</td>"; 
            echo "<td>" . htmlspecialchars($row->dateofbirth) . "</td>"; 
            echo "<td>" . htmlspecialchars($row->address) . "</td>"; 
            echo "<td>" . htmlspecialchars($row->gender) . "</td>"; 
            echo "<td>" . htmlspecialchars($row->registrationtime) . "</td>"; 
           
            
            echo "</tr>";
        }

        
        $totalRecords = $result->num_rows;
        echo "<tr><td colspan='10' style='text-align: center;'>Total Records: $totalRecords</td></tr>";
    } else {
        echo "<tr><td colspan='10'>No records found.</td></tr>";
    }

    $conn->close();
  ?>
</tbody>

        </table>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="assets/js/main.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
