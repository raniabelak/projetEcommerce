<?php
session_start();
include('connexionDB.php');

if (!isset($_SESSION['client'])) {
    header("Location: login.php?message=Please log in first.");
    exit();
}

$clientId = $_SESSION['client'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    $checkQuery = "SELECT * FROM cart WHERE client_id = ? AND product_id = ?";
    $stmt = $mysqlconnect->prepare($checkQuery);
    $stmt->bind_param('ii', $clientId, $product_id);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    if ($checkResult->num_rows > 0) {
        $updateQuery = "UPDATE cart SET quantity = quantity + ? WHERE client_id = ? AND product_id = ?";
        $updateStmt = $mysqlconnect->prepare($updateQuery);
        $updateStmt->bind_param('iii', $quantity, $clientId, $product_id);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        $insertQuery = "INSERT INTO cart (client_id, product_id, quantity) VALUES (?, ?, ?)";
        $insertStmt = $mysqlconnect->prepare($insertQuery);
        $insertStmt->bind_param('iii', $clientId, $product_id, $quantity);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    
    header("Location: $referer?message=Product added to cart successfully");
    exit();
}

$mysqlconnect->close();
?>