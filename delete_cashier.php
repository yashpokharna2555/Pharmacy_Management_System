<?php
session_start();
include_once('connect_db.php');

if (isset($_SESSION['username'])) {
    $id = $_SESSION['admin_id'];
    $user = $_SESSION['username'];
} else {
    header("location:http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php");
    exit();
}

$id = $_GET['cashier_id'];

// Replace these with your actual database connection details
$hostname = "localhost";
$username = "root";
$password = "";
$database = "pharmacy";

// Create a MySQLi connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use prepared statements to prevent SQL injection
$sql = "DELETE FROM cashier WHERE cashier_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

// Execute the statement
$stmt->execute();

// Close the statement and connection
$stmt->close();
$conn->close();

header("location:admin_cashier.php");
?>
