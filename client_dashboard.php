Fixed Client Dashboard Page

<?php
session_start();

if (!isset($_SESSION['client'])) {
    header("Location: login.php?message=Please log in first.");
    exit();
}

include('connexionDB.php');

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

// Initialize arrays for filtering
$where_conditions = array();
$params = array();
$types = '';

// Filter by name
if (isset($_GET['search_name']) && !empty($_GET['search_name'])) {
    $where_conditions[] = "product LIKE ?";
    $params[] = "%" . $_GET['search_name'] . "%";
    $types .= 's';
}

// Filter by price range
if (isset($_GET['min_price']) && $_GET['min_price'] !== '') {
    $where_conditions[] = "price >= ?";
    $params[] = floatval($_GET['min_price']);
    $types .= 'd';
}

if (isset($_GET['max_price']) && $_GET['max_price'] !== '') {
    $where_conditions[] = "price <= ?";
    $params[] = floatval($_GET['max_price']);
    $types .= 'd';
}

// Build the query
$productQuery = "SELECT * FROM product";
if (!empty($where_conditions)) {
    $productQuery .= " WHERE " . implode(" AND ", $where_conditions);
}

// Add sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
switch ($sort) {
    case 'price_asc':
        $productQuery .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $productQuery .= " ORDER BY price DESC";
        break;
    case 'name_desc':
        $productQuery .= " ORDER BY product DESC";
        break;
    default:
        $productQuery .= " ORDER BY product ASC";
}

// Prepare and execute the statement
$stmt = mysqli_prepare($mysqlconnect, $productQuery);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$productResult = mysqli_stmt_get_result($stmt);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #6c63ff;
            --text-color: #333;
            --background-color: #f9f9f9;
            --button-hover-color: #5750e5;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
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

        section {
            margin-top: 80px;
            padding: 4rem 20px;
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .filter-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-form input,
        .filter-form select {
            padding: 10px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 200px;
        }

        .filter-form button {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .filter-form button:hover {
            background-color: var(--button-hover-color);
        }

        .clear-filters {
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #666;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
        }

        .clear-filters:hover {
            background-color: #555;
        }

        .shop-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 2rem;
        }

        .product-box {
            background-color: #fff;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
            position: relative;
        }

        .product-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .product-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .price {
            font-weight: 500;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .add-cart {
            padding: 10px;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            position: absolute;
            bottom: 10px;
            right: 10px;
            transition: background-color 0.3s ease;
        }

        .add-cart:hover {
            background-color: var(--button-hover-color);
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

<section>
    <div class="container">
        <!-- Filter Form -->
        <form class="filter-form" method="GET">
            <input type="text" name="search_name" placeholder="Search by name"
                value="<?php echo isset($_GET['search_name']) ? htmlspecialchars($_GET['search_name']) : ''; ?>">
            
            <input type="number" name="min_price" placeholder="Min price" step="0.01"
                value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
            
            <input type="number" name="max_price" placeholder="Max price" step="0.01"
                value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">

            <select name="sort">
                <option value="name_asc" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
            </select>

            <button type="submit">Apply Filters</button>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="clear-filters">Clear Filters</a>
        </form>

        <div class="shop-content">
            <?php while ($row = mysqli_fetch_assoc($productResult)): ?>
                <div class="product-box">
                    <a href="product_details.php?product_id=<?= $row['id']; ?>">
                        <img class="product-img" src="<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['product']); ?>">
                    </a>
                    <div class="product-title"><?= htmlspecialchars($row['product']); ?></div>
                    <div class="price">$<?= number_format((float)$row['price'], 2); ?></div>
                    <form method="POST" action="add_to_cart.php">
                        <input type="hidden" name="product_id" value="<?= $row['id']; ?>">
                        <input type="hidden" name="product_name" value="<?= $row['product']; ?>">
                        <input type="hidden" name="product_price" value="<?= $row['price']; ?>">
                        <input type="hidden" name="product_image" value="<?= $row['image']; ?>">
                        <input type="hidden" name="product_description" value="<?= $row['description']; ?>">
                        <button type="submit" name="add_to_cart" class="add-cart">Add to Cart</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_free_result($result);
mysqli_close($mysqlconnect);
?>
