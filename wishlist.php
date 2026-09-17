<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
include "config/database.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$userId = (int) $_SESSION["user_id"];
$username = $_SESSION["username"] ?? "User";
$profilePicture = $_SESSION["profile_picture"] ?? "";
$wishlistProducts = [];
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        p.id,
        p.product_name,
        p.description,
        p.price,
        p.image,
        p.stock,
        p.category
     FROM wishlist w
     INNER JOIN products p
        ON w.product_id = p.id
     WHERE w.user_id = ?
       AND p.is_deleted = 0
     ORDER BY w.id DESC"
);
if ($stmt) {
    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $userId
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result(
        $stmt,
        $id,
        $productName,
        $description,
        $price,
        $image,
        $stock,
        $category
    );
    while (mysqli_stmt_fetch($stmt)) {
        $wishlistProducts[] = [
            "id" => $id,
            "product_name" => $productName,
            "description" => $description,
            "price" => $price,
            "image" => $image,
            "stock" => $stock,
            "category" => $category
        ];
    }
    mysqli_stmt_close($stmt);
}
$wishlistCount = count($wishlistProducts);
$profileImage = "";
if (!empty($profilePicture)) {
    $profileImage = "images/profile/" . $profilePicture;
}
function renderWishlistProduct(array $product): void
{
    $productId = (int) ($product["id"] ?? 0);
    $productName = htmlspecialchars(
        $product["product_name"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
    $description = htmlspecialchars(
        $product["description"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
    $category = htmlspecialchars(
        $product["category"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
    $price = number_format(
        (float) ($product["price"] ?? 0),
        2
    );
    $stock = (int) ($product["stock"] ?? 0);
    $image = trim($product["image"] ?? "");
    if ($image !== "") {
        if (strpos($image, "images/products/") !== 0 &&
            strpos($image, "http://") !== 0 &&
            strpos($image, "https://") !== 0) {
            $image = "images/products/" . ltrim($image, "/");
        }
    }
    echo '<div class="product-card">';
    echo '<a
            href="product_details.php?id=' . $productId . '"
            class="product-link">';
    echo '<div class="product-image">';
    if (!empty($image)) {
        echo '<img
                src="' . htmlspecialchars(
                    $image,
                    ENT_QUOTES,
                    "UTF-8"
                ) . '"
                alt="' . $productName . '">';
    } else {
        echo '<div class="no-image">No Image</div>';
    }
    echo '</div>';
    echo '</a>';
    echo '<div class="product-info">';
    if ($category !== "") {
        echo '<div class="product-category">' .
            $category .
            '</div>';
    }
    echo '<h3>' .
        $productName .
        '</h3>';
    echo '<p>' .
        $description .
        '</p>';
    echo '</div>';
    echo '<div class="product-bottom">';
    echo '<div class="product-details">';
    echo '<div class="product-price">
            Rs. ' . $price . '
          </div>';
    if ($stock > 0) {
        echo '<div class="stock available">
                In Stock: ' . $stock . '
              </div>';
    } else {
        echo '<div class="stock out-of-stock">
                Out of Stock
              </div>';
    }
    echo '</div>';
    echo '<div class="product-actions">';
    echo '<button
            type="button"
            class="wishlist-btn"
            data-id="' . $productId . '"
            title="Remove from wishlist">
            ♥ Wishlisted
          </button>';
    if ($stock > 0) {
        echo '<button
                type="button"
                class="add-cart"
                data-id="' . $productId . '">
                Add to Cart
              </button>';
    } else {
        echo '<button
                type="button"
                class="add-cart disabled"
                disabled>
                Out of Stock
              </button>';
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist | Inknest</title>
    <link rel="stylesheet" href="css/home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/chat.css?v=<?php echo time(); ?>">
    <style>
        .wishlist-page-header {
            width: 100%;
            margin: 0 0 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .wishlist-page-header h2 {
            margin: 0 0 6px;
            font-size: 28px;
            font-weight: 500;
            line-height: 1.3;
        }
        .wishlist-page-header p {
            margin: 0;
            color: #777;
            font-size: 14px;
        }
        .continue-shopping {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 15px;
            border-radius: 5px;
            background: #111;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            white-space: nowrap;
        }
        .continue-shopping:hover {
            background: #222;
        }
        .wishlist-btn.wishlisted {
            background: #111;
            color: #fff;
        }
        .wishlist-btn.wishlisted:hover {
            background: #222;
        }
        .empty-wishlist {
            width: 100%;
            min-height: 300px;
            padding: 50px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .empty-wishlist-icon {
            margin-bottom: 15px;
            font-size: 50px;
            line-height: 1;
        }
        .empty-wishlist h2 {
            margin: 0 0 10px;
            font-size: 24px;
            font-weight: 500;
        }
        .empty-wishlist p {
            margin: 0 0 20px;
            color: #777;
            font-size: 14px;
        }
        .wishlist-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .wishlist-view-btn {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            color: #111;
            text-decoration: none;
            font-size: 25px;
            border-radius: 50%;
        }
        .wishlist-view-btn:hover,
        .wishlist-view-btn.active {
            background: #f5f5f5;
        }
        .wishlist-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #111;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
        }
        @media (max-width: 650px) {
            .wishlist-page-header {
                align-items: flex-start;
                flex-direction: column;
            }
            .wishlist-page-header h2 {
                font-size: 25px;
            }
            .continue-shopping {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <h1>Inknest</h1>
    <form class="search" action="home.php" method="GET">
        <input type="text" name="search" placeholder="Search products..." value="" autocomplete="off">
        <button type="submit">Search</button>
    </form>
    <div class="nav-links">
        <div class="user-info">
            <?php if (!empty($profileImage)): ?>
                <img src="<?php echo htmlspecialchars(
                        $profileImage,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>"
                    alt="Profile"
                    class="profile-picture"
                >
            <?php else: ?>
                <div class="profile-placeholder">
                    <?php
                    echo strtoupper(
                        substr($username, 0, 1)
                    );
                    ?>
                </div>
            <?php endif; ?>
            <span class="username">
                <?php
                echo htmlspecialchars(
                    $username,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </span>
        </div>
        <a href="home.php">Products</a>
        <a href="wishlist.php" class="wishlist-nav-link">❤️</a>
        <a href="manage_profile.php">Manage Profile</a>
        <a href="cart.php" class="cart">
            Cart
            <span id="cartCount">
                0
            </span>
        </a>
        <a href="logout.php">Logout</a>
    </div>
</nav>
<main class="container">
    <div class="wishlist-page-header">
        <div>
            <h2>My Wishlist</h2>
            <p>
                <?php echo $wishlistCount; ?>
                <?php
                echo $wishlistCount === 1
                    ? "item"
                    : "items";
                ?>
                saved
            </p>
        </div>
        <a href="home.php" class="continue-shopping">Continue Shopping</a>
    </div>
    <?php if (!empty($wishlistProducts)): ?>
        <div class="product-grid">
            <?php foreach ($wishlistProducts as $product): ?>
                <?php
                renderWishlistProduct($product);
                ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-wishlist">
            <div class="empty-wishlist-icon">
                ♡
            </div>
            <h2>Your Wishlist is Empty</h2>
            <p>Save your favorite tattoo products here.</p>
            <a href="home.php" class="continue-shopping">Browse Products</a>
        </div>
    <?php endif; ?>
</main>
<div class="inknest-chat-button" id="chatButton">
    💬
</div>
<div class="inknest-chat-box" id="chatBox">
    <div class="chat-header">
        <span>Inknest Chat</span>
        <button type="button" id="closeChat">×</button>
    </div>
    <div class="chat-messages" id="chatMessages"></div>
    <div class="chat-input-area">
        <input type="text" id="chatInput" placeholder="Type a message..." autocomplete="off">
        <button type="button" id="sendChat">Send</button>
    </div>
</div>
<script>
window.currentUserId = <?php echo $userId; ?>;
window.wishlistProducts = <?php
    echo json_encode(
        $wishlistProducts,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
?>;
</script>
<script src="js/home.js?v=<?php echo time(); ?>" defer></script>
<script src="js/chat.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>