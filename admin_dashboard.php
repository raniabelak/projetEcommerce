<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include('connexionDB.php');

if (isset($_GET['delete'])) {
    $productId = $_GET['delete'];
    $deleteQuery = "DELETE FROM product WHERE id = '$productId'";
    if (mysqli_query($mysqlconnect, $deleteQuery)) {
        $message = "Product deleted successfully!";
    } else {
        $message = "Error deleting product: " . mysqli_error($mysqlconnect);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $product = $_POST['product'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
        $imageNewName = uniqid('', true) . '.' . $imageExtension;
        $imagePath = './img/' . $imageNewName;

        if (move_uploaded_file($imageTmpName, $imagePath)) {
            $image = $imagePath;
        } else {
            $message = "Failed to upload the image.";
        }
    }

    if (empty($product) || empty($description) || empty($price) || empty($image)) {
        $message = "All fields are required.";
    } else {
        if (!is_numeric($price)) {
            $message = "Price must be a valid number.";
        } else {
            if ($id) {
                $query = "UPDATE product SET product='$product', description='$description', price='$price', image='$image' WHERE id='$id'";
                if (mysqli_query($mysqlconnect, $query)) {
                    $message = "Product updated successfully!";
                } else {
                    $message = "Error updating product: " . mysqli_error($mysqlconnect);
                }
            } else {
                $query = "INSERT INTO product (product, description, price, image) VALUES ('$product', '$description', '$price', '$image')";
                if (mysqli_query($mysqlconnect, $query)) {
                    $message = "Product added successfully!";
                } else {
                    $message = "Error adding product: " . mysqli_error($mysqlconnect);
                }
            }
        }
    }
}

$query = "SELECT * FROM product";
$result = mysqli_query($mysqlconnect, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($mysqlconnect));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Products</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    header {
        background-color: #fff;
        padding: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .logo {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
    #logout-btn {
        background-color: #6c63ff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    #logout-btn:hover {
        background-color: #5a56e3;
    }

    .dashboard-container {
        width: 80%;
        margin: 30px auto;
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    h1 {
        font-size: 36px;
        text-align: center;
    }
    .product-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 16px;
    }
    .product-table th, .product-table td {
        padding: 12px;
        text-align: center;
        border: 1px solid #ddd;
    }
    .product-table th {
        background-color: #6c63ff; 
        color: white;
    }
    .product-table td img {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }
    .btn {
        padding: 8px 12px;
        background-color: #6c63ff; 
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
    }
    .btn:hover {
        background-color: #5a56e3;
    }
    .action-buttons {
        display: flex;
        justify-content: space-around;
    }
    .add-product-btn {
        background-color: #6c63ff;
    }
    .add-product-btn:hover {
        background-color: #5a56e3; 
    }
    .form-container {
        margin-top: 20px;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .form-container input {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .form-container button {
        padding: 10px 15px;
        background-color: #6c63ff; 
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .form-container button:hover {
        background-color: #5a56e3; 
    }
    .message {
        color: green;
        text-align: center;
        font-weight: bold;
    }
</style>

</head>
<body>

<header>
    <div class="nav">
        <div class="logo">Alibaba</div>
        <a href="logout.php">
            <button id="logout-btn">Logout</button>
        </a>
    </div>
</header>

<div class="dashboard-container">
    <h1>Welcome Back Admin</h1>

    <?php if (isset($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo htmlspecialchars($product['product']); ?></td>
                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                    <td><?php echo "$" . number_format((float)preg_replace('/[^0-9\.]/', '', $product['price']), 2); ?></td>
                    <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image"></td>
                    <td class="action-buttons">
                        <a href="admin_dashboard.php?edit=<?php echo $product['id']; ?>" class="btn">Edit</a>
                        <a href="admin_dashboard.php?delete=<?php echo $product['id']; ?>" class="btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    if (isset($_GET['edit'])) {
        $productId = $_GET['edit'];
        $editQuery = "SELECT * FROM product WHERE id = '$productId'";
        $editResult = mysqli_query($mysqlconnect, $editQuery);
        $productToEdit = mysqli_fetch_assoc($editResult);
    ?>
        <div class="form-container">
            <h2>Edit Product</h2>
            <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $productToEdit['id']; ?>">
                <input type="text" name="product" value="<?php echo htmlspecialchars($productToEdit['product']); ?>" placeholder="Product Name" required>
                <input type="text" name="description" value="<?php echo htmlspecialchars($productToEdit['description']); ?>" placeholder="Product Description" required>
                <input type="number" step="0.01" name="price" value="<?php echo $productToEdit['price']; ?>" placeholder="Price" required>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit">Update Product</button>
            </form>
        </div>
    <?php } else { ?>
        <div class="form-container">
            <h2>Add New Product</h2>
            <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="product" placeholder="Product Name" required>
                <input type="text" name="description" placeholder="Product Description" required>
                <input type="number" step="0.01" name="price" placeholder="Price" required>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit">Add Product</button>
            </form>
        </div>
    <?php } ?>
</div>

</body>
</html>

<?php
mysqli_free_result($result);
mysqli_close($mysqlconnect);
?>
