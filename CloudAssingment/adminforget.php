<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nemail']) && !empty($_POST['nemail'])) {
        $mgnemail = $_POST['nemail'];

        $sql = "SELECT * FROM manager WHERE mgnemail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mgnemail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['mgnemail'] = $mgnemail;
            header("Location: adminnew.php");
            exit();
        } else {
            echo "<script>alert('Invalid email. Please try again.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please fill in the email field.');</script>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forget Password</title>
    <link rel="stylesheet" href="adminforget.css">
    <link href="https://fonts.googleapis.com/css2?family=Asap&display=swap" rel="stylesheet">
    
</head>
<body>

    <form class="login" method="post">
        <h1 style="text-align:center; color:#444;">Golden Gown</h1>
        <table class="form-table">
  <tr>
    <td colspan="2">
      Enter your Email:
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <input type="text" name="nemail" placeholder="Enter your Email" required>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <button type="submit" name="sb">Next</button>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="right-link">
      <a href="admin_login.php">Back to Login</a>
    </td>
  </tr>
</table>

    </form>

</body>
</html>
