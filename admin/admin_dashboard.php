<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$adminUsername = $_SESSION["admin_username"];
$productQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM products"
);

$productData = mysqli_fetch_assoc($productQuery);
$totalProducts = $productData["total"];
$userQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);
$userData = mysqli_fetch_assoc($userQuery);
$totalUsers = $userData["total"];
$orderQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM orders"
);

if ($orderQuery) {
    $orderData = mysqli_fetch_assoc($orderQuery);
    $totalOrders = $orderData["total"];
} else {
    $totalOrders = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Inknest</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?php echo time(); ?>">
    <script src="../js/admin_dashboard.js" defer></script>
</head>

<body>
<aside class="sidebar">
    <h1>Inknest</h1>
    <p class="admin-label">ADMIN PANEL</p>
    <nav>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="products.php">Products</a>
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
            <h2>Dashboard</h2>
            <p>Welcome back, <?php echo htmlspecialchars($adminUsername); ?></p>
        </div>
    </header>

    <section class="stats">
        <div class="stat-card">
            <span>Products</span>
            <strong><?php echo $totalProducts; ?></strong>
        </div>

        <div class="stat-card">
            <span>Users</span>
            <strong><?php echo $totalUsers; ?></strong>
        </div>

        <div class="stat-card">
            <span>Orders</span>
            <strong><?php echo $totalOrders; ?></strong>
        </div>
    </section>

    <section class="section">
        <h3>Quick Actions</h3>
        <div class="actions">
            <a href="add_product.php">Add Product</a>
            <a href="products.php">Manage Products</a>
            <a href="orders.php">View Orders</a>
            <a href="users.php">View Users</a>
        </div>
    </section>
</main>
</body>
</html>