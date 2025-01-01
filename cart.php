<?php
session_start();

if (!isset($_SESSION['client'])) {
    header("Location: login.php?message=Please log in first.");
    exit();
}

include('connexionDB.php');

$clientId = $_SESSION['client'];
$email = $_SESSION['email'];

$cartQuery = "SELECT c.quantity, p.product, p.price, p.image, p.id AS product_id 
             FROM cart c 
             JOIN product p ON c.product_id = p.id 
             WHERE c.client_id = '$clientId'";
$cartResult = mysqli_query($mysqlconnect, $cartQuery);
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
* {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background-color: #f4f3f9;
    color: #333;
}

.container {
    max-width: 1068px;
    margin: 50px auto;
    padding: 0 20px;
}

h1 {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    text-align: center;
    color: #333; 
}

.cart-content {
    margin-top: 2rem;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 1rem;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
    transition: all 0.3s ease-in-out;
}

.cart-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.cart-item-img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
}

.cart-item-details {
    flex: 1;
}

.product-title {
    font-size: 1.2rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color:rgb(1, 1, 1); 
}

.cart-item-price {
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: #6c63ff; 
}

.cart-item-quantity {
    width: 80px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    text-align: center;
}

.delete-button {
    background-color: #6c63ff; 
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 1rem;
}

.delete-button:hover {
    background-color: #333; 
}

.cart-total {
    margin-top: 2rem;
    padding: 1rem;
    border-top: 2px solid #ddd;
    font-size: 1.5rem;
    font-weight: bold;
    text-align: right;
    background-color: #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    color:rgb(2, 2, 2); 
}

.empty-cart {
    text-align: center;
    padding: 2rem;
    font-size: 1.2rem;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.continue-shopping {
    display: inline-block;
    background-color: #6c63ff;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 1rem;
    font-size: 1.1rem;
    text-align: center;
}

.continue-shopping:hover {
    background-color: #7b1fa2; 
}

.cart-item-details .quantity-controls {
    margin-top: 0.5rem;
}

.cart-item-details .quantity-controls input {
    width: 80px;
    height: 35px;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    text-align: center;
    box-sizing: border-box;
}

.cart-total a {
    display: inline-block;
    background-color: #8e24aa; 
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 1rem;
    font-size: 1.1rem;
    text-align: center;
}

.cart-total a:hover {
    background-color:#721998; 
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Your Shopping Cart</h1>
        <div class="cart-content">
            <?php
            if (mysqli_num_rows($cartResult) > 0) {
                while ($cartItem = mysqli_fetch_assoc($cartResult)) {
                    $itemTotal = floatval($cartItem['price']) * intval($cartItem['quantity']);
                    $total += $itemTotal;
                    ?>
                    <div class="cart-item" data-price="<?= $cartItem['price']; ?>">
                        <img src="<?= htmlspecialchars($cartItem['image']); ?>" alt="<?= htmlspecialchars($cartItem['product']); ?>" class="cart-item-img">
                        <div class="cart-item-details">
                            <div class="product-title"><?= htmlspecialchars($cartItem['product']); ?></div>
                            <div class="cart-item-price">$<span class="item-total"><?= number_format($itemTotal, 2); ?></span></div>
                            <div class="quantity-controls">
                                <input type="number" 
                                       min="1" 
                                       value="<?= $cartItem['quantity']; ?>" 
                                       class="cart-item-quantity"
                                       data-product-id="<?= $cartItem['product_id']; ?>"
                                       onchange="updateQuantity(this)">
                            </div>
                        </div>
                        <form method="POST" action="remove_from_cart.php">
                            <input type="hidden" name="product_id" value="<?= $cartItem['product_id']; ?>">
                            <button type="submit" name="remove_from_cart" class="delete-button">Remove</button>
                        </form>
                    </div>
                    <?php
                }
                ?>
                <div class="cart-total">
                    Total: $<span id="cart-total"><?= number_format($total, 2); ?></span>
                </div>
                <?php
            } else {
                ?>
                <div class="empty-cart">
                    <p>Your cart is empty!</p>
                    <a href="client_dashboard.php" class="continue-shopping">Continue Shopping</a>
                </div>
                <?php
            }
            ?>
        </div>
        <?php if (mysqli_num_rows($cartResult) > 0): ?>
            <div style="text-align: right; margin-top: 1rem;">
                <a href="client_dashboard.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function updateQuantity(input) {
        const productId = input.getAttribute('data-product-id');
        const quantity = input.value;
        const cartItem = input.closest('.cart-item');
        const price = parseFloat(cartItem.getAttribute('data-price'));
        const itemTotal = price * quantity;
        
        cartItem.querySelector('.item-total').textContent = itemTotal.toFixed(2);
        
        let total = 0;
        document.querySelectorAll('.item-total').forEach(item => {
            total += parseFloat(item.textContent);
        });
        document.getElementById('cart-total').textContent = total.toFixed(2);
        
        fetch('update_cart_quantity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}&update_cart=true`
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);  
        })
        .catch(error => {
            console.error('Error updating cart quantity:', error);
        });
    }
</script>

</body>
</html>

<?php
mysqli_free_result($cartResult);
mysqli_close($mysqlconnect);
?>