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

$salesQuery = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total_amount), 0) AS total_sales FROM orders"
);

if ($salesQuery) {
    $salesData = mysqli_fetch_assoc($salesQuery);
    $totalSales = $salesData["total_sales"];
} else {
    $totalSales = 0;
}

$deliveryQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'delivered'"
);

if ($deliveryQuery) {
    $deliveryData = mysqli_fetch_assoc($deliveryQuery);
    $totalDeliveries = $deliveryData["total"];
} else {
    $totalDeliveries = 0;
}

$stockQuery = mysqli_query(
    $conn,
    "SELECT 
        DATE(created_at) AS stock_date,
        SUM(stock) AS total_stock
     FROM products
     GROUP BY DATE(created_at)
     ORDER BY stock_date ASC
     LIMIT 10"
);

$stockLabels = [];
$stockValues = [];

if ($stockQuery) {
    while ($stockRow = mysqli_fetch_assoc($stockQuery)) {
        $stockLabels[] = date("M d", strtotime($stockRow["stock_date"]));
        $stockValues[] = (int)$stockRow["total_stock"];
    }
}

if (empty($stockLabels)) {
    $stockLabels = ["No Data"];
    $stockValues = [0];
}
?> 
 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Admin Dashboard | Inknest</title> 
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?php echo time(); ?>"> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <a href="user_profiles.php">User Profiles</a> 
        <a href="user_log.php">User Activity</a> 
        <a href="product_reviews.php">Product Reviews</a> 
        <a href="admin_wishlist.php">Customer Wishlist</a> 
        <a href="bill.php">Bills</a> 
        <a href="stock_management.php">Stock of Products</a> 
        <a href="stock_history.php">Stock History</a> 
        <a href="chat.php">Chat<span id="adminChatSidebarBadge" class="admin-chat-badge">
            0</span></a> 
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
        <div class="stat-card"> 
            <span>Total Sales</span> 
            <strong>Rs. <?php echo number_format($totalSales, 2); ?></strong> 
        </div>
        <div class="stat-card"> 
            <span>Delivered Orders</span> 
            <strong><?php echo $totalDeliveries; ?></strong> 
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

    <section class="section stock-chart-section">
        <h3>Stock Overview</h3>
        <div class="chart-container">
            <canvas id="stockChart"></canvas>
        </div>
    </section>
</main>
<script>
const stockLabels = <?php echo json_encode($stockLabels); ?>;
const stockValues = <?php echo json_encode($stockValues); ?>;
const stockChart = document.getElementById("stockChart");

new Chart(stockChart, {
    type: "line",
    data: {
        labels: stockLabels,
        datasets: [{
            label: "Total Stock",
            data: stockValues,
            borderColor: "#111",
            backgroundColor: "rgba(17, 17, 17, 0.1)",
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: "#111",
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>
</body> 
</html>