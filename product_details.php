<?php
session_start();

if (!isset($_SESSION['client'])) {
    header("Location: login.php?message=Please log in first.");
    exit();
}

include('connexionDB.php');

$clientId = $_SESSION['client'];

// Retrieve the product_id from the URL
if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];

    // Fetch the product details from the database using prepared statement
    $productQuery = "SELECT * FROM product WHERE id = ?";
    $stmt = mysqli_prepare($mysqlconnect, $productQuery);
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $productResult = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($productResult) > 0) {
        $product = mysqli_fetch_assoc($productResult);
    } else {
        header("Location: client_dashboard.php?message=Product not found.");
        exit();
    }
} else {
    header("Location: client_dashboard.php?message=Invalid product.");
    exit();
}

// Fetch the client details using prepared statement
$clientId = $_SESSION['client'];
$email = $_SESSION['email'];
$query = "SELECT * FROM client WHERE id = ?";
$stmt = mysqli_prepare($mysqlconnect, $query);
mysqli_stmt_bind_param($stmt, "i", $clientId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $client = mysqli_fetch_assoc($result);
    $name = $client['nom'];
} else {
    header("Location: login.php?message=Client not found.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            scroll-padding-top: 2rem;
            scroll-behavior: smooth;
            box-sizing: border-box;
            list-style: none;
            text-decoration: none;
        }

        :root {
            --primary-color: #6c63ff;
            --button-hover-color: #5750e5;
            --bg-color: #fff;
        }

        img {
            width: 100%;
        }

        body {
            color: var(--text-color);
        }

        .container {
            max-width: 1068px;
            margin: auto;
            width: 100%;
            padding: 0 20px;
        }

        header {
            background-color: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            padding: 10px 30px;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 100;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            color: var(--text-color);
            font-weight: bold;
        }

        .nav-items {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        #cart-icon {
            font-size: 1.8rem;
            cursor: pointer;
        }

        #logout-btn {
            padding: 8px 16px;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        #logout-btn:hover {
            background-color: var(--button-hover-color);
        }

        .product-details {
            display: flex;
            gap: 2rem;
            margin-top: 80px;
        }

        .back-button {
            display: inline-block;
            margin-top: 80px;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: var(--button-hover-color);
        }

        .back-button i {
            margin-right: 8px;
        }

        .product-img-large {
            width: 400px;
            height: auto;
            object-fit: cover;
        }

        .product-info {
            flex: 1;
        }

        .product-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .price {
            font-weight: 500;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .description {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .quantity-controls input {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .add-cart {
            background-color: #6c63ff;
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .add-cart:hover {
            background-color: #5750e5;
            transform: scale(1.05);
        }

        .add-cart:active {
            background-color: #4a40c5;
            transform: scale(1);
        }
    </style>
</head>
<body>

<header>
    <div class="nav">
        <div class="logo">Alibaba</div>
        <div class="nav-items">
            <a href="cart.php">
                <i class="fa fa-shopping-cart" style="font-size:36px"></i>
            </a>
            <a href="logout.php">
                <button id="logout-btn">Logout</button>
            </a>
        </div>
    </div>
</header>

<div class="container">
    <a href="client_dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i>Back to Products
    </a>
    <div class="product-details">
        <img class="product-img-large" src="<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['product']); ?>">
        <div class="product-info">
            <div class="product-title"><?= htmlspecialchars($product['product']); ?></div>
            <div class="price">$<?= number_format((float)$product['price'], 2); ?></div>
            <div class="description"><?= htmlspecialchars($product['description']); ?></div>

            <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
                <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['product']); ?>">
                <input type="hidden" name="product_price" value="<?= htmlspecialchars($product['price']); ?>">
                <input type="hidden" name="product_image" value="<?= htmlspecialchars($product['image']); ?>">
                <input type="hidden" name="product_description" value="<?= htmlspecialchars($product['description']); ?>">

                <div class="quantity-controls">
                    <input type="number" name="quantity" min="1" value="1">
                </div>

                <button type="submit" name="add_to_cart" class="add-cart">Add to Cart</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_free_result($result);
mysqli_free_result($productResult);
mysqli_close($mysqlconnect);
?>