<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

include "config/database.php";
require_once "config/recommendation.php";

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

$products = [];

if ($search !== "" && $category !== "") {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id,
            product_name,
            category,
            description,
            price,
            image,
            stock
         FROM products
         WHERE is_deleted = 0
           AND LOWER(TRIM(category)) = LOWER(TRIM(?))
           AND (
                product_name LIKE ?
                OR description LIKE ?
                OR category LIKE ?
           )
         ORDER BY id DESC
         LIMIT 50"
    );

    if (!$stmt) {
        die(
            "Product query failed: " .
            htmlspecialchars(
                mysqli_error($conn),
                ENT_QUOTES,
                "UTF-8"
            )
        );
    }

    $searchTerm = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $category,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }

    mysqli_stmt_close($stmt);

} elseif ($search !== "") {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id,
            product_name,
            category,
            description,
            price,
            image,
            stock
         FROM products
         WHERE is_deleted = 0
           AND (
                product_name LIKE ?
                OR description LIKE ?
                OR category LIKE ?
           )
         ORDER BY id DESC
         LIMIT 50"
    );

    if (!$stmt) {
        die(
            "Search query failed: " .
            htmlspecialchars(
                mysqli_error($conn),
                ENT_QUOTES,
                "UTF-8"
            )
        );
    }

    $searchTerm = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }

    mysqli_stmt_close($stmt);

} elseif ($category !== "") {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id,
            product_name,
            category,
            description,
            price,
            image,
            stock
         FROM products
         WHERE is_deleted = 0
           AND LOWER(TRIM(category)) = LOWER(TRIM(?))
         ORDER BY id DESC
         LIMIT 50"
    );

    if (!$stmt) {
        die(
            "Category query failed: " .
            htmlspecialchars(
                mysqli_error($conn),
                ENT_QUOTES,
                "UTF-8"
            )
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $category
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }

    mysqli_stmt_close($stmt);

} else {

    $result = mysqli_query(
        $conn,
        "SELECT
            id,
            product_name,
            category,
            description,
            price,
            image,
            stock
         FROM products
         WHERE is_deleted = 0
         ORDER BY id DESC"
    );

    if (!$result) {
        die(
            "Product query failed: " .
            htmlspecialchars(
                mysqli_error($conn),
                ENT_QUOTES,
                "UTF-8"
            )
        );
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    mysqli_free_result($result);
}

if ($category !== "") {
    $recommendedProducts = array_slice($products, 0, 8);
} elseif ($search !== "") {
    $recommendedProducts = [];
}

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

        if (
            str_starts_with(
                $image,
                "images/products/"
            )
        ) {
            $imagePath = $image;
        } else {
            $imagePath =
                "images/products/" . $image;
        }
    }
?>

<div class="product-card">

    <a
        href="product_details.php?id=<?php echo $productId; ?>"
        class="product-link"
    >

        <div class="product-image">

            <?php if (!empty($imagePath)): ?>

                <img
                    src="<?php
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
                    ?>"
                >

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

            <button
                type="button"
                class="wishlist-btn"
                data-id="<?php echo $productId; ?>"
            >

                <?php
                echo $isWishlisted
                    ? "♥ Wishlisted"
                    : "♡ Wishlist";
                ?>

            </button>

            <?php if ($stock > 0): ?>

                <button
                    type="button"
                    class="add-cart"
                    data-id="<?php echo $productId; ?>"
                >
                    Add to Cart
                </button>

            <?php else: ?>

                <button
                    type="button"
                    class="add-cart disabled"
                    disabled
                >
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Home | Inknest</title>

    <link
        rel="stylesheet"
        href="css/home.css?v=<?php echo time(); ?>"
    >

    <style>
        .navbar .search {
            display: flex;
            align-items: center;
            gap: 0;
        }

        .navbar .search input {
            min-width: 0;
        }

        .navbar .search button {
            order: 2;
        }

        .navbar .search .category-select {
            order: 3;
            height: 44px;
            margin-left: 0;
        }

        @media (max-width: 900px) {
            .navbar .search {
                flex-wrap: wrap;
            }

            .navbar .search input {
                flex: 1 1 220px;
            }
        }
    </style>

    <link
        rel="stylesheet"
        href="css/chat.css?v=<?php echo time(); ?>"
    >

    <script
        src="js/home.js?v=<?php echo time(); ?>"
        defer
    ></script>

</head>

<body>

<nav class="navbar">

    <h1>
        Inknest
    </h1>

    <form
        class="search"
        method="GET"
        action="home.php"
    >

        <input
            type="text"
            name="search"
            placeholder="Search products..."
            value="<?php
                echo htmlspecialchars(
                    $search,
                    ENT_QUOTES,
                    "UTF-8"
                );
            ?>"
        >

        <button type="submit">
            Search
        </button>

        <select
            name="category"
            class="category-select"
            aria-label="Filter by category"
            onchange="this.form.submit()"
        >
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
                    echo strcasecmp(trim($category), trim($categoryName)) === 0
                        ? "selected"
                        : "";
                    ?>
                >
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

    </form>

    <div class="nav-links">

        <div class="user-info">

            <?php if (!empty($profilePicture)): ?>

                <img
                    src="images/profile/<?php
                        echo htmlspecialchars(
                            $profilePicture,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"
                    alt="Profile Picture"
                    class="profile-picture"
                >

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

        </div>

        <a href="profile.php">
            Manage Profile
        </a>

        <a
            href="cart.php"
            class="cart"
        >

            Cart

            <span id="cartCount">
                0
            </span>

        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>

<main class="container">

    <?php if (
        !empty($recommendedProducts) &&
        ($search === "" || $category !== "")
    ): ?>

        <section class="products-section">

            <div class="heading">

                <h2>
                    Recommended for You
                </h2>

            </div>

            <div class="product-grid">

                <?php foreach (
                    $recommendedProducts
                    as $product
                ): ?>

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

            <?php if (
                $search !== "" &&
                $category !== ""
            ): ?>

                <h2>

                    <?php
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

                <h2>

                    Search results for

                    "<?php
                    echo htmlspecialchars(
                        $search,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>"

                </h2>

            <?php elseif ($category !== ""): ?>

                <h2>

                    <?php
                    echo htmlspecialchars(
                        $category,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                    Products

                </h2>

            <?php else: ?>

                <h2>
                    Products
                </h2>

            <?php endif; ?>

        </div>

        <?php if (!empty($products)): ?>

            <div class="product-grid">

                <?php foreach (
                    $products
                    as $product
                ): ?>

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

                <h3>
                    No products found
                </h3>

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

<button
    type="button"
    class="inknest-chat-button"
    id="inknestChatButton"
    aria-label="Open chat"
>
    💬

    <span
        class="inknest-chat-badge"
        id="inknestChatBadge"
    >
        0
    </span>

</button>

<div
    class="inknest-chat-box"
    id="inknestChatBox"
>

    <div class="inknest-chat-header">

        <div class="inknest-chat-title">

            <strong>
                Inknest Support
            </strong>

            <span>
                We are here to help
            </span>

        </div>

        <button
            type="button"
            class="inknest-chat-close"
            id="inknestChatClose"
        >
            x
        </button>

    </div>

    <div
        class="inknest-chat-messages"
        id="inknestChatMessages"
    >

        <div class="inknest-chat-empty">
            Loading chat...
        </div>

    </div>

    <div class="inknest-chat-input-area">

        <input
            type="text"
            id="inknestChatInput"
            class="inknest-chat-input"
            placeholder="Type a message..."
            maxlength="2000"
            autocomplete="off"
        >

        <button
            type="button"
            id="inknestChatSend"
            class="inknest-chat-send"
        >
            ➤
        </button>

    </div>

</div>

<script src="js/chat.js?v=<?php echo time(); ?>"></script>

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

