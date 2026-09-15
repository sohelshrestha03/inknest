<?php 
session_start(); 
include "../config/database.php"; 
if (!isset($_SESSION["admin_id"])) { 
    header("Location: admin_login.php"); 
    exit(); 
} 
if (isset($_GET["delete"])) { 
    $id = intval($_GET["delete"]); 
    if ($id > 0) { 
        $delete = mysqli_prepare( 
            $conn, 
            "UPDATE products SET is_deleted = 1 WHERE id = ?" 
        ); 
        if ($delete) { 
            mysqli_stmt_bind_param( 
                $delete, 
                "i", 
                $id 
            ); 

            mysqli_stmt_execute($delete); 
            mysqli_stmt_close($delete); 
        } 
    } 
    header("Location: products.php"); 
    exit(); 
} 
$result = mysqli_query( 
    $conn, 
    "SELECT id, product_name, category, description, price, image, stock 
     FROM products 
     WHERE is_deleted = 0 
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
<aside class="sidebar" id="sidebar"> 
    <button type="button" class="sidebar-toggle" id="sidebarToggle">☰</button>
    <h1>Inknest</h1> 
    <p class="admin-label">ADMIN PANEL</p> 
    <nav> 
        <a href="admin_dashboard.php">Dashboard</a> 
        <a href="products.php" class="active">Products</a> 
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
        <a href="chat.php"> 
            Chat 
            <span  id="adminChatBadge"  class="admin-chat-badge"> 
                0 
            </span> 
        </a> 
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
        <?php if ($result && mysqli_num_rows($result) > 0): ?> 
            <table> 
                <thead> 
                    <tr> 
                        <th>Image</th> 
                        <th>Product</th> 
                        <th>Category</th> 
                        <th>Description</th> 
                        <th>Price</th> 
                        <th>Stock</th> 
                        <th>Action</th> 
                    </tr> 
                </thead> 
                <tbody> 
                <?php while ($product = mysqli_fetch_assoc($result)): ?> 
                    <tr> 
                        <td> 
                            <?php if (!empty($product["image"])): ?> 
                                <img  src="../images/products/<?php echo htmlspecialchars($product["image"]); ?>" 
                                    alt="Product" 
                                    class="product-image"> 
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
                            <strong> 
                                <?php 
                                echo htmlspecialchars( 
                                    $product["category"] 
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
                        <td> 
                            <?php 
                            echo htmlspecialchars( 
                                $product["stock"] 
                            ); 
                            ?> 
                        </td> 
                        <td class="actions"> 
                            <a href="edit_product.php?id=<?php echo $product["id"]; ?>"  class="edit">Edit</a> 
                            <a href="products.php?delete=<?php echo $product["id"]; ?>"  class="delete delete-product">Delete</a> 
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