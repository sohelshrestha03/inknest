<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM products WHERE id = ?"
    );
    mysqli_stmt_bind_param(
        $delete,
        "i",
        $id
    );
    mysqli_stmt_execute($delete);
    mysqli_stmt_close($delete);
    header("Location: products.php");
    exit();
}

$result = mysqli_query(
    $conn,
    "SELECT id, product_name, description, price, image
     FROM products
     ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | Inknest Admin</title>
    <link rel="stylesheet" href="../css/products.css?v=<?php echo time(); ?>">
    <script src="../js/products.js" defer></script>
</head>

<body>
<aside class="sidebar">
    <h1>Inknest</h1>
    <p class="admin-label">ADMIN PANEL</p>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="products.php" class="active">Products</a>
        <a href="add_product.php">Add Product</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="admin_logout.php">Logout</a>
    </div>
</aside>

<main class="main">
    <header class="header">
        <div>
            <h2>Products</h2>
            <p>Manage your store products</p>
        </div>

        <a href="add_product.php" class="add-button">+ Add Product</a>
    </header>

    <div class="product-container">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <?php if (!empty($product["image"])): ?>
                                <img src="../images/products/<?php echo htmlspecialchars($product["image"]);?>" alt="Product" class="product-image">

                            <?php else: ?>
                                <div class="no-image">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $product["product_name"]
                                );
                                ?>
                            </strong>
                        </td>
                        <td>
                            <?php
                            echo htmlspecialchars(
                                $product["description"]
                            );
                            ?>
                        </td>
                        <td>
                            Rs.
                            <?php
                            echo number_format(
                                $product["price"],
                                2
                            );
                            ?>
                        </td>
                        <td class="actions">
                            <a href="edit_product.php?id=<?php echo $product["id"];?>" class="edit">Edit</a>
                            <a href="products.php?delete=<?php echo $product["id"];?>" class="delete delete-product">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div class="empty">
                <h3>No products found</h3>
                <p>Add your first product to your store.</p>
                <a href="add_product.php">Add Product</a>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>