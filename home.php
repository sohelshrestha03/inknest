<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
include "config/database.php";
require_once "config/recommendation.php";
require_once "config/sorting.php";
require_once "config/searching.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$userId = (int) $_SESSION["user_id"];
$username = $_SESSION["username"] ?? "";
$profilePicture = "";
$profileStmt = mysqli_prepare(
    $conn,
    "SELECT profile_picture
     FROM user_profiles
     WHERE user_id = ?
     LIMIT 1"
);
if ($profileStmt) {
    mysqli_stmt_bind_param(
        $profileStmt,
        "i",
        $userId
    );
    mysqli_stmt_execute($profileStmt);
    mysqli_stmt_bind_result(
        $profileStmt,
        $profilePictureValue
    );
    if (mysqli_stmt_fetch($profileStmt)) {
        $profilePicture = $profilePictureValue ?? "";
    }
    mysqli_stmt_close($profileStmt);
}
$wishlistProducts = [];
$wishlistStmt = mysqli_prepare(
    $conn,
    "SELECT product_id
     FROM wishlist
     WHERE user_id = ?"
);
if ($wishlistStmt) {
    mysqli_stmt_bind_param(
        $wishlistStmt,
        "i",
        $userId
    );
    mysqli_stmt_execute($wishlistStmt);
    mysqli_stmt_bind_result(
        $wishlistStmt,
        $wishlistProductId
    );
    while (mysqli_stmt_fetch($wishlistStmt)) {
        $wishlistProducts[(int) $wishlistProductId] = true;
    }
    mysqli_stmt_close($wishlistStmt);
}
$recommendedProducts = [];
try {
    $recommendedProducts = getRecommendedProducts(
        $conn,
        $userId,
        8
    );
    if (!is_array($recommendedProducts)) {
        $recommendedProducts = [];
    }
} catch (Throwable $e) {
    $recommendedProducts = [];
}
$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";
$category = isset($_GET["category"])
    ? trim($_GET["category"])
    : "";
$categories = [];
$categoryResult = mysqli_query(
    $conn,
    "SELECT DISTINCT category
     FROM products
     WHERE is_deleted = 0
       AND category IS NOT NULL
       AND TRIM(category) <> ''
     ORDER BY category ASC"
);
if ($categoryResult) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $row["category"];
    }
    mysqli_free_result($categoryResult);
}
$products = searchProducts(
    $conn,
    $search,
    $category,
    $orderBy,
    50
);
if ($category !== "") {
    $recommendedProducts = array_slice(
        $products,
        0,
        8
    );
} elseif ($search !== "") {
    $recommendedProducts = [];
}
sortProducts(
    $recommendedProducts,
    $sort
);
$notificationRecommendations = $recommendedProducts;
function renderProductCard(
    array $product,
    array $wishlistProducts
): void {
    $productId = (int) ($product["id"] ?? 0);
    $stock = (int) ($product["stock"] ?? 0);
    $productName = $product["product_name"] ?? "";
    $category = $product["category"] ?? "";
    $description = $product["description"] ?? "";
    $price = (float) ($product["price"] ?? 0);
    $image = $product["image"] ?? "";
    $isWishlisted = isset(
        $wishlistProducts[$productId]
    );
    $imagePath = "";
    if (!empty($image)) {
        if (str_starts_with($image, "images/products/")) {
            $imagePath = $image;
        } else {
            $imagePath = "images/products/" . $image;
        }
    }
?>
<div class="product-card">
    <a href="product_details.php?id=<?php echo $productId; ?>" class="product-link">
        <div class="product-image">
            <?php if (!empty($imagePath)): ?>
                <img src="<?php
                        echo htmlspecialchars(
                            $imagePath,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"
                    alt="<?php
                        echo htmlspecialchars(
                            $productName,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>">
            <?php else: ?>
                <div class="no-image">
                    No Image
                </div>
            <?php endif; ?>
        </div>
        <div class="product-info">
            <?php if (!empty($category)): ?>
                <div class="product-category">
                    <?php
                    echo htmlspecialchars(
                        $category,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </div>
            <?php endif; ?>
            <h3>
                <?php
                echo htmlspecialchars(
                    $productName,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </h3>
            <p>
                <?php
                echo htmlspecialchars(
                    $description,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </p>
        </div>
    </a>
    <div class="product-bottom">
        <div class="product-details">
            <div class="product-price">
                Rs.
                <?php
                echo number_format(
                    $price,
                    2
                );
                ?>
            </div>
            <?php if ($stock > 0): ?>
                <div class="stock available">
                    Available:
                    <strong>
                        <?php echo $stock; ?>
                    </strong>
                </div>
            <?php else: ?>
                <div class="stock out-of-stock">
                    Out of Stock
                </div>
            <?php endif; ?>
        </div>
        <div class="product-actions">
            <button type="button" class="wishlist-btn" data-id="<?php echo $productId; ?>">
                <?php
                echo $isWishlisted
                    ? "♥ Wishlisted"
                    : "♡ Wishlist";
                ?>
            </button>
            <?php if ($stock > 0): ?>
                <button type="button" class="add-cart" data-id="<?php echo $productId; ?>">
                    Add to Cart
                </button>
            <?php else: ?>
                <button type="button" class="add-cart disabled" disabled>
                    Out of Stock
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Inknest</title>
    <link rel="stylesheet" href="css/home.css?v=<?php echo time(); ?>">
    <style>
        .filter-area {
            display: flex;
            align-items: center;
            width: 620px;
            max-width: 100%;
            margin-top: 18px;
            margin-bottom: 30px;
        }
        .filter-area select {
            height: 44px;
            box-sizing: border-box;
            background: #fff;
            font-size: 16px;
            padding: 0 18px;
            cursor: pointer;
            outline: none;
        }
        .filter-area .category-select {
            width: 50%;
            border: 1px solid #aaa;
            border-radius: 7px 0 0 7px;
        }
        .filter-area .sort-select {
            width: 50%;
            border: 1px solid #aaa;
            border-left: none;
            border-radius: 0 7px 7px 0;
        }
        .navbar .search {
            display: flex;
            align-items: center;
            gap: 0;
            flex: 1;
            max-width: 760px;
        }
        .navbar .search input {
            flex: 1;
            min-width: 180px;
            height: 44px;
            box-sizing: border-box;
        }
        .navbar .search button {
            order: 2;
            height: 44px;
            min-width: 102px;
            box-sizing: border-box;
        }
        .notification-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            margin-left: 8px;
        }
        .notification-btn {
            position: relative;
            width: 38px;
            height: 38px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .notification-btn:hover {
            opacity: 0.75;
        }
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 17px;
            height: 17px;
            padding: 0 4px;
            background: #e53935;
            color: #fff;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 17px;
        }
        .notification-dropdown {
            display: none;
            position: absolute;
            top: 48px;
            right: -10px;
            width: 320px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            z-index: 1000;
            overflow: hidden;
        }
        .notification-dropdown.show {
            display: block;
        }
        .notification-header {
            padding: 14px 16px;
            border-bottom: 1px solid #eee;
            font-size: 15px;
        }
        .notification-list {
            max-height: 350px;
            overflow-y: auto;
        }
        .notification-item {
            display: flex;
            gap: 12px;
            padding: 12px 14px;
            text-decoration: none;
            color: #111;
            border-bottom: 1px solid #eee;
        }
        .notification-item:hover {
            background: #f7f7f7;
        }
        .notification-item-image {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 6px;
            background: #eee;
            flex-shrink: 0;
        }
        .notification-item-info {
            min-width: 0;
        }
        .notification-item-name {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .notification-item-category {
            font-size: 12px;
            color: #777;
        }
        .notification-empty {
            padding: 20px;
            text-align: center;
            color: #777;
            font-size: 13px;
        }
        @media (max-width: 900px) {
            .navbar .search {
                flex-wrap: wrap;
            }
            .navbar .search input {
                flex: 1 1 220px;
            }
            .filter-area {
                width: 100%;
            }
        }
        @media (max-width: 600px) {
            .filter-area {
                flex-direction: column;
            }
            .filter-area .category-select,
            .filter-area .sort-select {
                width: 100%;
                border: 1px solid #aaa;
                border-radius: 7px;
            }
            .filter-area .sort-select {
                margin-top: 8px;
            }
            .notification-dropdown {
                position: fixed;
                top: 70px;
                right: 10px;
                left: 10px;
                width: auto;
            }
        }
    </style>
    <link rel="stylesheet" href="css/chat.css?v=<?php echo time(); ?>">
    <script src="js/home.js?v=<?php echo time(); ?>" defer></script>
</head>
<body>
<nav class="navbar">
    <h1>Inknest</h1>
    <form class="search" method="GET" action="home.php">
        <input type="text" name="search"
            placeholder="Search products..."
            value="<?php
                echo htmlspecialchars(
                    $search,
                    ENT_QUOTES,
                    "UTF-8"
                );
            ?>">
        <button type="submit">Search</button>
    </form>
    <div class="nav-links">
        <div class="user-info" data-user-id="<?php echo $userId; ?>">
            <?php if (!empty($profilePicture)): ?>
                <img src="images/profile/<?php
                        echo htmlspecialchars(
                            $profilePicture,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"
                    alt="Profile Picture"
                    class="profile-picture">
            <?php else: ?>
                <div class="profile-placeholder">
                    <?php
                    $firstLetter = strtoupper(
                        substr(
                            trim($username),
                            0,
                            1
                        )
                    );
                    echo htmlspecialchars(
                        $firstLetter ?: "U",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </div>
            <?php endif; ?>
            <span class="username">
                Hi,
                <?php
                echo htmlspecialchars(
                    $username,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </span>
            <div class="notification-wrapper">
                <button type="button"
                    class="notification-btn"
                    id="notificationBtn"
                    aria-label="New products and recommendations"
                    aria-expanded="false">
                    🔔
                    <span class="notification-badge" id="notificationBadge" style="display:none;">
                        0
                    </span>
                </button>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <strong>
                            New Items
                        </strong>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-empty">
                            No new items
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="profile.php">Manage Profile</a>
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
    <?php if (
        !empty($recommendedProducts) &&
        ($search === "" || $category !== "")
    ): ?>
        <section class="products-section">
            <div class="heading">
                <h2>Recommended for You</h2>
            </div>
            <form class="filter-area" method="GET" action="home.php">
                <input type="hidden" name="search"
                    value="<?php
                        echo htmlspecialchars(
                            $search,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>">
                <select name="category" class="category-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $categoryName): ?>
                        <option
                            value="<?php
                                echo htmlspecialchars(
                                    $categoryName,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                            ?>"
                            <?php
                            echo strcasecmp(
                                trim($category),
                                trim($categoryName)
                            ) === 0
                                ? "selected"
                                : "";
                            ?>>
                            <?php
                            echo htmlspecialchars(
                                $categoryName,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="newest"
                        <?php
                        echo $sort === "newest"
                            ? "selected"
                            : "";
                        ?>>
                        Newest
                    </option>
                    <option
                        value="oldest"
                        <?php
                        echo $sort === "oldest"
                            ? "selected"
                            : "";
                        ?>>
                        Oldest
                    </option>
                    <option value="price_low"
                        <?php
                        echo $sort === "price_low"
                            ? "selected"
                            : "";
                        ?>>
                        Price: Low to High
                    </option>
                    <option value="price_high"
                        <?php
                        echo $sort === "price_high"
                            ? "selected"
                            : "";
                        ?>>
                        Price: High to Low
                    </option>
                    <option value="name_az"
                        <?php
                        echo $sort === "name_az"
                            ? "selected"
                            : "";
                        ?>>
                        Name: A to Z
                    </option>
                    <option value="name_za"
                        <?php
                        echo $sort === "name_za"
                            ? "selected"
                            : "";
                        ?>>
                        Name: Z to A
                    </option>
                </select>
            </form>
            <div class="product-grid">
                <?php foreach ($recommendedProducts as $product): ?>
                    <?php
                    renderProductCard(
                        $product,
                        $wishlistProducts
                    );
                    ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <section class="products-section">
        <div class="heading">
            <?php if ($search !== "" && $category !== ""): ?>
                <h2><?php
                    echo htmlspecialchars(
                        $category,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                    results for
                    "<?php
                    echo htmlspecialchars(
                        $search,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>"
                </h2>
            <?php elseif ($search !== ""): ?>
                <h2>Search results for
                    "<?php
                    echo htmlspecialchars(
                        $search,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>"
                </h2>
            <?php elseif ($category !== ""): ?>
                <h2><?php
                    echo htmlspecialchars(
                        $category,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                    Products
                </h2>
            <?php else: ?>
                <h2>Products</h2>
            <?php endif; ?>
        </div>
        <form class="filter-area" method="GET" action="home.php">
            <input type="hidden" name="search"
                value="<?php
                    echo htmlspecialchars(
                        $search,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>">
            <select name="category" class="category-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $categoryName): ?>
                    <option value="<?php
                            echo htmlspecialchars(
                                $categoryName,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                        <?php
                        echo strcasecmp(
                            trim($category),
                            trim($categoryName)
                        ) === 0
                            ? "selected"
                            : "";
                        ?>>
                        <?php
                        echo htmlspecialchars(
                            $categoryName,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort" class="sort-select" onchange="this.form.submit()">
                <option value="newest"
                    <?php
                    echo $sort === "newest"
                        ? "selected"
                        : "";
                    ?>>
                    Newest
                </option>
                <option value="oldest"
                    <?php
                    echo $sort === "oldest"
                        ? "selected"
                        : "";
                    ?>>
                    Oldest
                </option>
                <option value="price_low"
                    <?php
                    echo $sort === "price_low"
                        ? "selected"
                        : "";
                    ?>>
                    Price: Low to High
                </option>
                <option value="price_high"
                    <?php
                    echo $sort === "price_high"
                        ? "selected"
                        : "";
                    ?>>
                    Price: High to Low
                </option>
                <option value="name_az"
                    <?php
                    echo $sort === "name_az"
                        ? "selected"
                        : "";
                    ?>>
                    Name: A to Z
                </option>
                <option value="name_za"
                    <?php
                    echo $sort === "name_za"
                        ? "selected"
                        : "";
                    ?>>
                    Name: Z to A
                </option>
            </select>
        </form>
        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php
                    renderProductCard(
                        $product,
                        $wishlistProducts
                    );
                    ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <h3>No products found</h3>
                <p>
                    <?php if ($search !== ""): ?>
                        Try searching for another product.
                    <?php elseif ($category !== ""): ?>
                        No products are available in this category.
                    <?php else: ?>
                        No products are available right now.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </section>
</main>
<button type="button" class="inknest-chat-button" id="inknestChatButton" aria-label="Open chat">
    💬
    <span class="inknest-chat-badge" id="inknestChatBadge">
        0
    </span>
</button>
<div class="inknest-chat-box" id="inknestChatBox">
    <div class="inknest-chat-header">
        <div class="inknest-chat-title">
            <strong>
                Inknest Support
            </strong>
            <span>
                We are here to help
            </span>
        </div>
        <button type="button" class="inknest-chat-close" id="inknestChatClose">x</button>
    </div>
    <div class="inknest-chat-messages" id="inknestChatMessages">
        <div class="inknest-chat-empty">
            Loading chat...
        </div>
    </div>
    <div class="inknest-chat-input-area">
        <input type="text" id="inknestChatInput" class="inknest-chat-input" placeholder="Type a message..." maxlength="2000" autocomplete="off">
        <button type="button" id="inknestChatSend" class="inknest-chat-send">➤</button>
    </div>
</div>
<script src="js/chat.js?v=<?php echo time(); ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const notificationBtn = document.getElementById("notificationBtn");
    const notificationBadge = document.getElementById("notificationBadge");
    const notificationDropdown = document.getElementById("notificationDropdown");
    const notificationList = document.getElementById("notificationList");
    const userInfo = document.querySelector(".user-info");
    if (!notificationBtn ||
        !notificationBadge ||
        !notificationDropdown ||
        !notificationList ||
        !userInfo) {
        return;
    }
    const userId=userInfo.dataset.userId;
    const productStorageKey="inknest_last_product_id_" + userId;
    const recommendationStorageKey="inknest_seen_recommendations_" + userId;
    let currentLatestId = 0;
    let newProductItems = [];
    let recommendationItems=[];
    const recommendationData=<?php
        echo json_encode(
            array_map(
                function ($product) {
                    return [
                        "id" => (int) ($product["id"] ?? 0),
                        "product_name" => $product["product_name"] ?? "",
                        "category" => $product["category"] ?? "",
                        "image" => $product["image"] ?? ""
                    ];
                },
                $notificationRecommendations
            ),
            JSON_HEX_TAG |
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_AMP
        );
        ?>;
    notificationBtn.addEventListener(
        "click",
        function (event) {
            event.stopPropagation();
            const isOpen=notificationDropdown.classList.toggle("show");
            notificationBtn.setAttribute(
                "aria-expanded",
                isOpen ? "true" : "false"
            );
            if (isOpen) {
                if (currentLatestId > 0) {
                    localStorage.setItem(
                        productStorageKey,
                        currentLatestId
                    );
                }
                const recommendationIds=recommendationItems.map(
                        function (item) {
                            return String(item.id);
                        }
                    );
                if (recommendationIds.length > 0) {
                    localStorage.setItem(
                        recommendationStorageKey,
                        JSON.stringify(
                            recommendationIds
                        )
                    );
                }
                recommendationItems = [];
                newProductItems = [];
                updateNotificationBadge();
            }
        }
    );
    document.addEventListener(
        "click",
        function (event) {
            if (!notificationDropdown.contains(event.target) && !notificationBtn.contains(event.target)) {
                notificationDropdown.classList.remove("show");
                notificationBtn.setAttribute(
                    "aria-expanded",
                    "false"
                );
            }
        }
    );
    function escapeHTML(value) {
        const div = document.createElement("div");
        div.textContent = value ?? "";
        return div.innerHTML;
    }
    function getSeenRecommendationIds() {
        try {
            const stored =localStorage.getItem(recommendationStorageKey);
            if (!stored) {
                return [];
            }
            const parsed = JSON.parse(stored);
            return Array.isArray(parsed)
                ? parsed.map(String)
                : [];
        } catch (error) {
            return [];
        }
    }
    function getUnreadRecommendations() {
        const seenIds=getSeenRecommendationIds();
        return recommendationData.filter(
            function (item) {
                return (
                    item.id &&
                    !seenIds.includes(
                        String(item.id)
                    )
                );
            }
        );
    }
    function updateNotificationBadge() {
        const totalNotifications=newProductItems.length+recommendationItems.length;
        if (totalNotifications > 0) {
            notificationBadge.textContent=totalNotifications > 9
                    ? "9+"
                    : totalNotifications;
            notificationBadge.style.display = "flex";
        } else {
            notificationBadge.style.display = "none";
        }
    }
    function createNotificationItem(
        item,
        isRecommendation
    ) {
        let imageHTML = "";
        if (item.image) {
            let imagePath = item.image;
            if (!imagePath.startsWith("images/products/")) {
                imagePath="images/products/" + imagePath;
            }
            imageHTML = `
                <img
                    src="${escapeHTML(imagePath)}"
                    class="notification-item-image"
                    alt=""
                >
            `;
        } else {
            imageHTML = `
                <div class="notification-item-image"></div>
            `;
        }
        const link=document.createElement("a");
        link.href ="product_details.php?id=" + encodeURIComponent(item.id);
        link.className="notification-item";
        const categoryText=isRecommendation
                ? "Recommended for you"
                : (
                    item.category ||
                    "New Product"
                );
        link.innerHTML = `
            ${imageHTML}
            <div class="notification-item-info">
                <div class="notification-item-name">
                    ${escapeHTML(
                        item.product_name
                    )}
                </div>
                <div class="notification-item-category">
                    ${escapeHTML(
                        categoryText
                    )}
                </div>
            </div>
        `;
        return link;
    }
    function renderNotifications() {
        notificationList.innerHTML = "";
        if (recommendationItems.length === 0 && newProductItems.length === 0) {
            notificationList.innerHTML = `
                <div class="notification-empty">
                    No new items
                </div>
            `;
            return;
        }
        recommendationItems.forEach(
            function (item) {
                notificationList.appendChild(
                    createNotificationItem(
                        item,
                        true
                    )
                );
            }
        );
        newProductItems.forEach(
            function (item) {
                notificationList.appendChild(
                    createNotificationItem(
                        item,
                        false
                    )
                );
            }
        );
    }
    function loadNotifications() {
        const lastId=parseInt(
                localStorage.getItem(
                    productStorageKey
                ) || "0",
                10
            );
        recommendationItems=getUnreadRecommendations();
        fetch(
            "new_items.php?last_id=" +
            lastId,
            {
                method: "GET",
                cache: "no-store"
            }
        )
        .then(
            function (response) {
                return response.json();
            }
        )
        .then(
            function (data) {
                if (!data.success) {
                    newProductItems = [];
                    renderNotifications();
                    updateNotificationBadge();
                    return;
                }
                currentLatestId=parseInt(
                        data.latest_id || 0,
                        10
                    );
                if (lastId === 0 && currentLatestId > 0) {
                    localStorage.setItem(
                        productStorageKey,
                        currentLatestId
                    );
                    newProductItems=[];
                } else {
                    newProductItems=data.new_items || [];
                }
                recommendationItems=getUnreadRecommendations();
                renderNotifications();
                updateNotificationBadge();
            }
        )
        .catch(
            function () {
                recommendationItems=getUnreadRecommendations();
                renderNotifications();
                updateNotificationBadge();
            }
        );
    }
    loadNotifications();
    setInterval(
        loadNotifications,
        30000
    );
});
</script>
<footer class="footer">
    <div class="footer-content">
        <div class="footer-brand">
            <h2>
                Inknest
            </h2>
            <p>
                Your trusted online shopping destination.
            </p>
        </div>
        <div class="footer-links">
            <div class="footer-contact">
                <h3>
                    Contact Us
                </h3>
                <p>
                    <strong>
                        Phone:
                    </strong>
                    +977-9800000000
                </p>
                <p>
                    <strong>
                        Email:
                    </strong>
                    support@inknest.com
                </p>
                <p>
                    <strong>
                        Address:
                    </strong>
                    Kathmandu, Nepal
                </p>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>
            &copy;
            <?php echo date("Y"); ?>
            Inknest.
            All rights reserved.
        </p>
    </div>
</footer>
</body>
</html>