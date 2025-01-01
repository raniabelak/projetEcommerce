<?php 
session_start();
include('connexionDB.php');

if (!isset($_SESSION['client'])) {
    echo "Please log in first.";
    exit();
}

$clientId = $_SESSION['client'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['quantity']) && isset($_POST['update_cart'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($productId > 0 && $quantity > 0) {
        $updateQuery = "UPDATE cart 
                        SET quantity = $quantity 
                        WHERE client_id = '$clientId' 
                        AND product_id = '$productId'";

        if (mysqli_query($mysqlconnect, $updateQuery)) {
            echo "Quantity updated successfully.";
        } else {
            echo "Failed to update quantity.";
        }
    } else {
        echo "Invalid product or quantity.";
    }
    exit();
}
?>
