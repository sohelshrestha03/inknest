<?php
session_start();
include "../config/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}
$adminUsername = $_SESSION["admin_username"] ?? "Admin";
$wishlistQuery = mysqli_query(
    $conn,
    "SELECT
        w.id,
        w.user_id,
        w.product_id,
        w.created_at,
        u.user_name,
        u.email,
        p.product_name,
        p.price,
        p.image
     FROM wishlist AS w
     LEFT JOIN users AS u
        ON w.user_id = u.id
     LEFT JOIN products AS p
        ON w.product_id = p.id
     ORDER BY w.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Wishlist | Inknest</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?php echo time(); ?>">
    <style>
        .wishlist-container {
            margin-top: 30px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
            overflow-x: auto;
        }
        .wishlist-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }
        .wishlist-table th,
        .wishlist-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: middle;
        }
        .wishlist-table th {
            background: #f7f7f7;
            font-weight: 600;
        }
        .wishlist-table tr:last-child td {
            border-bottom: none;
        }
        .wishlist-product {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .wishlist-product img {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #eee;
        }
        .no-image {
            width: 55px;
            height: 55px;
            border-radius: 6px;
            border: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #777;
        }
        .no-wishlist {
            padding: 30px;
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>
<aside class="sidebar">
    <h1>Inknest</h1>
    <p class="admin-label">
        ADMIN PANEL
    </p>
    <nav>
           <a href="admin_dashboard.php">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="add_product.php" >Add Product</a>
            <a href="orders.php">Orders</a>
            <a href="users.php">Users</a>
            <a href="user_profiles.php">User Profiles</a>
            <a href="user_log.php">User Activity</a>
            <a href="product_reviews.php">Product Reviews</a>
            <a href="admin_wishlist.php" class="active">Customer Wishlist</a>
            <a href="bill.php">Bills</a>
            <a href="stock_management.php">Stock of Products</a>
            <a href="stock_history.php">Stock History</a>
            <a href="chat.php">
                Chat<span id="adminChatBadge" class="admin-chat-badge">
                    0
                </span>
            </a>
    </nav>
    <div class="sidebar-bottom">
        <a href="admin_logout.php">
            Logout
        </a>
    </div>
</aside>
<main class="main">
    <header class="header">
        <div>
            <h2>
                Customer Wishlist
            </h2>
            <p>
                View products wishlisted by customers
            </p>
        </div>
    </header>
    <div class="wishlist-container">
        <?php if (
            $wishlistQuery &&
            mysqli_num_rows($wishlistQuery) > 0
        ): ?>
            <table class="wishlist-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>
                            Customer
                        </th>
                        <th>
                            Email
                        </th>
                        <th>
                            Product
                        </th>
                        <th>
                            Price
                        </th>
                        <th>
                            Added Date
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $number = 1;
                    while ($row = mysqli_fetch_assoc($wishlistQuery)):
                        $imagePath = "";
                        if (!empty($row["image"])) {
                            $fileName = basename(
                                str_replace(
                                    "\\",
                                    "/",
                                    $row["image"]
                                )
                            );
                            $imagePath ="../images/products/" .$fileName;
                        }
                    ?>
                        <tr>
                            <td>
                                <?php echo $number++; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(
                                    $row["user_name"] ?? "Unknown User"
                                ); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(
                                    $row["email"] ?? "-"
                                ); ?>
                            </td>
                            <td>
                                <div class="wishlist-product">
                                    <?php if (
                                        !empty($imagePath)
                                    ): ?>
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>"
                                            alt="<?php echo htmlspecialchars($row["product_name"] ?? "Product"); ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            No Image
                                        </div>
                                    <?php endif; ?>
                                    <span>
                                        <?php echo htmlspecialchars(
                                            $row["product_name"] ?? "Deleted Product"
                                        ); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                Rs.
                                <?php echo number_format(
                                    (float)($row["price"] ?? 0),
                                    2
                                ); ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($row["created_at"])) {
                                    echo date(
                                        "M d, Y h:i A",
                                        strtotime(
                                            $row["created_at"]
                                        )
                                    );
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-wishlist">
                No wishlist items found.
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>