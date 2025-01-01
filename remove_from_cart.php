<?php
session_start();
include('connexionDB.php');

if (!isset($_SESSION['client'])) {
    header("Location: login.php?message=Please log in first.");
    exit();
}

$clientId = $_SESSION['client'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];

    $deleteQuery = "DELETE FROM cart WHERE client_id = '$clientId' AND product_id = '$product_id'";
    mysqli_query($mysqlconnect, $deleteQuery);

    header("Location: cart.php");  // Ensure cart.php is the page you're using for the cart display
    exit();
}

mysqli_close($mysqlconnect);
?>
