<?php 
session_start(); 
include "../config/database.php"; 
if (!isset($_SESSION["admin_id"])) { 
    header("Location: admin_login.php"); 
    exit(); 
} 
$adminUsername = $_SESSION["admin_username"]; 
$reviewQuery = mysqli_query( 
    $conn, 
    "SELECT 
        pr.id, 
        pr.product_id, 
        pr.user_id, 
        pr.parent_review_id, 
        pr.rating, 
        pr.comment, 
        pr.created_at, 
        u.user_name, 
        p.product_name 
     FROM product_reviews pr 
     LEFT JOIN users u ON pr.user_id = u.id 
     LEFT JOIN products p ON pr.product_id = p.id 
     ORDER BY pr.created_at DESC" 
); 
?> 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Product Reviews | Inknest</title> 
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?php echo time(); ?>"> 
    <script src="../js/admin_dashboard.js" defer></script> 
</head> 
<body> 
<aside class="sidebar" id="sidebar"> 
    <button type="button" class="sidebar-toggle" id="sidebarToggle">☰</button>
    <h1>Inknest</h1> 
    <p class="admin-label">ADMIN PANEL</p> 
    <nav> 
        <a href="admin_dashboard.php">Dashboard</a> 
        <a href="products.php">Products</a> 
        <a href="add_product.php">Add Product</a> 
        <a href="orders.php">Orders</a> 
        <a href="users.php">Users</a> 
        <a href="user_profiles.php">User Profiles</a> 
        <a href="user_log.php">User Activity</a> 
        <a href="product_reviews.php" class="active">Product Reviews</a> 
        <a href="admin_wishlist.php">Customer Wishlist</a> 
        <a href="bill.php">Bills</a> 
        <a href="stock_management.php">Stock of Products</a> 
        <a href="stock_history.php">Stock History</a> 
        <a href="chat.php"> 
            Chat 
            <span 
                id="adminChatBadge" 
                class="admin-chat-badge"> 
                0 
            </span> 
        </a> 
    </nav> 
    <div class="sidebar-bottom"> 
        <a href="admin_logout.php"> Logout </a> 
    </div> 
</aside> 
<main class="main"> 
    <header class="header"> 
        <div> 
            <h2>Product Reviews</h2> 
            <p>View customer product reviews</p> 
        </div> 
    </header> 
    <section class="section"> 
        <h3>Product Reviews</h3> 
        <?php if ($reviewQuery && mysqli_num_rows($reviewQuery) > 0): ?> 
            <table class="review-table"> 
                <thead> 
                    <tr> 
                        <th>ID</th> 
                        <th>User</th> 
                        <th>Product</th> 
                        <th>Rating</th> 
                        <th>Comment</th> 
                        <th>Review Type</th> 
                        <th>Date & Time</th> 
                    </tr> 
                </thead> 
                <tbody> 
                    <?php while ($review = mysqli_fetch_assoc($reviewQuery)): ?> 
                        <tr> 
                            <td> 
                                <?php 
                                echo htmlspecialchars($review["id"]); 
                                ?> 
                            </td> 
                            <td> 
                                <?php 
                                echo htmlspecialchars( 
                                    $review["user_name"] ?? "Unknown User" 
                                ); 
                                ?> 
                            </td> 
                            <td> 
                                <?php 
                                echo htmlspecialchars( 
                                    $review["product_name"] ?? "Unknown Product" 
                                ); 
                                ?> 
                            </td> 
                            <td> 
                                <?php 
                                echo htmlspecialchars( 
                                    $review["rating"] ?? "-" 
                                ); 
                                ?> 
                            </td> 
                            <td> 
                                <?php 
                                echo htmlspecialchars( 
                                    $review["comment"] ?? "-" 
                                ); 
                                ?> 
                            </td> 
                            <td> 
                                <?php if (!empty($review["parent_review_id"])): ?> 
                                    Reply 
                                <?php else: ?> 
                                    Review 
                                <?php endif; ?> 
                            </td> 
                            <td> 
                                <?php 
                                echo date( 
                                    "M d, Y h:i A", 
                                    strtotime($review["created_at"]) 
                                ); 
                                ?> 
                            </td> 
                        </tr> 
                    <?php endwhile; ?> 
                </tbody> 
            </table> 
        <?php else: ?> 
            <p>No product reviews found.</p> 
        <?php endif; ?> 
    </section> 
</main> 
<style>
.sidebar {
    position: fixed;
    transition: transform 0.3s ease;
    overflow: hidden;
}
.sidebar-toggle {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 1002;
    width: 42px;
    height: 42px;
    border: none;
    border-radius: 6px;
    background: #111;
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sidebar.closed {
    transform: translateX(calc(-100% + 62px));
}
.main {
    transition: margin-left 0.3s ease;
}
.main.sidebar-closed {
    margin-left: 62px;
}
.sidebar.closed .sidebar-toggle {
    right: 10px;
}
.sidebar.closed h1,
.sidebar.closed .admin-label,
.sidebar.closed nav,
.sidebar.closed .sidebar-bottom {
    visibility: hidden;
}
</style>
<script>
const sidebar = document.getElementById("sidebar");
const sidebarToggle = document.getElementById("sidebarToggle");
const main = document.querySelector(".main");
sidebarToggle.addEventListener("click", function () {
    sidebar.classList.toggle("closed");
    main.classList.toggle("sidebar-closed");
    if (sidebar.classList.contains("closed")) {
        sidebarToggle.textContent = "☰";
    } else {
        sidebarToggle.textContent = "☰";
    }
});
</script>
</body> 
</html>