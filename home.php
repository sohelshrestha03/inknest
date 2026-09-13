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

$userId = (int)$_SESSION["user_id"];
$username = $_SESSION["username"] ?? "";
$profilePicture = "";

$profileSql = mysqli_prepare(
    $conn,
    "SELECT profile_picture
     FROM user_profiles
     WHERE user_id = ?"
);

if ($profileSql) {

    mysqli_stmt_bind_param(
        $profileSql,
        "i",
        $userId
    );

    mysqli_stmt_execute($profileSql);

    mysqli_stmt_bind_result(
        $profileSql,
        $profilePictureValue
    );

    if (mysqli_stmt_fetch($profileSql)) {
        $profilePicture = $profilePictureValue ?? "";
    }

    mysqli_stmt_close($profileSql);
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
        $wishlistProducts[(int)$wishlistProductId] = true;
    }

    mysqli_stmt_close($wishlistStmt);
}

$recommendedProducts = getRecommendedProducts(
    $conn,
    $userId,
    8
);

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if ($search !== "") {

    $sql = mysqli_prepare(
        $conn,
        "SELECT
            id,
            product_name,
            description,
            price,
            image,
            stock
         FROM products
         WHERE product_name LIKE ?
            OR description LIKE ?
         ORDER BY id DESC"
    );

    if (!$sql) {
        die("Product query failed.");
    }

    $searchTerm = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $sql,
        "ss",
        $searchTerm,
        $searchTerm
    );

    if (!mysqli_stmt_execute($sql)) {
        mysqli_stmt_close($sql);
        die("Product query failed.");
    }

    mysqli_stmt_bind_result(
        $sql,
        $searchId,
        $searchProductName,
        $searchDescription,
        $searchPrice,
        $searchImage,
        $searchStock
    );

    $products = [];

    while (mysqli_stmt_fetch($sql)) {

        $products[] = [
            "id" => (int)$searchId,
            "product_name" => $searchProductName,
            "description" => $searchDescription,
            "price" => (float)$searchPrice,
            "image" => $searchImage,
            "stock" => (int)$searchStock
        ];
    }

    mysqli_stmt_close($sql);

} else {

    $products = mysqli_query(
        $conn,
        "SELECT
            id,
            product_name,
            description,
            price,
            image,
            stock
         FROM products
         ORDER BY id DESC"
    );

    if (!$products) {
        die("Product query failed.");
    }
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

    <title>
        Home | Inknest
    </title>

    <link
        rel="stylesheet"
        href="css/home.css?v=<?php echo time(); ?>"
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
        method="get"
        action="home.php"
    >

        <input
            type="text"
            name="search"
            placeholder="Search products..."
            value="<?php echo htmlspecialchars(
                $search,
                ENT_QUOTES,
                "UTF-8"
            ); ?>"
        >

        <button type="submit">
            Search
        </button>

    </form>

    <div class="nav-links">

        <div class="user-info">

            <?php if (!empty($profilePicture)): ?>

                <img
                    src="images/profile/<?php echo htmlspecialchars(
                        $profilePicture,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>"
                    alt="Profile Picture"
                    class="profile-picture"
                >

            <?php else: ?>

                <div class="profile-placeholder">

                    <?php
                    echo htmlspecialchars(
                        strtoupper(
                            substr(
                                trim($username),
                                0,
                                1
                            )
                        ),
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
        $search === "" &&
        !empty($recommendedProducts)
    ): ?>

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

                $productId =
                    (int)$product["id"];

                $stock =
                    (int)$product["stock"];

                $isWishlisted =
                    isset(
                        $wishlistProducts[
                            $productId
                        ]
                    );

                ?>

                <div class="product-card">

                    <a
                        href="product_details.php?id=<?php echo $productId; ?>"
                        class="product-link"
                    >

                        <div class="product-image">

                            <?php if (
                                !empty($product["image"])
                            ): ?>

                                <img
                                    src="images/products/<?php echo htmlspecialchars(
                                        $product["image"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>"
                                    alt="<?php echo htmlspecialchars(
                                        $product["product_name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>"
                                >

                            <?php else: ?>

                                <div class="no-image">
                                    No Image
                                </div>

                            <?php endif; ?>

                        </div>

                        <div class="product-info">

                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $product["product_name"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </h3>

                            <p>

                                <?php
                                echo htmlspecialchars(
                                    $product["description"] ?? "",
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
                                    (float)$product["price"],
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

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <div class="heading">

        <?php if ($search !== ""): ?>

            <h2>

                Search results for

                "<?php echo htmlspecialchars(
                    $search,
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>"

            </h2>

        <?php else: ?>

            <h2>
                Products
            </h2>

        <?php endif; ?>

    </div>


    <div class="product-grid">

        <?php if ($search !== ""): ?>

            <?php if (!empty($products)): ?>

                <?php foreach ($products as $product): ?>

                    <?php

                    $productId =
                        (int)$product["id"];

                    $stock =
                        (int)$product["stock"];

                    $isWishlisted =
                        isset(
                            $wishlistProducts[
                                $productId
                            ]
                        );

                    ?>

                    <div class="product-card">

                        <a
                            href="product_details.php?id=<?php echo $productId; ?>"
                            class="product-link"
                        >

                            <div class="product-image">

                                <?php if (
                                    !empty($product["image"])
                                ): ?>

                                    <img
                                        src="images/products/<?php echo htmlspecialchars(
                                            $product["image"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>"
                                        alt="<?php echo htmlspecialchars(
                                            $product["product_name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>"
                                    >

                                <?php else: ?>

                                    <div class="no-image">
                                        No Image
                                    </div>

                                <?php endif; ?>

                            </div>

                            <div class="product-info">

                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["product_name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </h3>

                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["description"] ?? "",
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
                                        (float)$product["price"],
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

                <?php endforeach; ?>

            <?php else: ?>

                <div class="no-products">

                    <h3>
                        No products found
                    </h3>

                    <p>
                        Try searching for another product.
                    </p>

                </div>

            <?php endif; ?>

        <?php else: ?>

            <?php if (mysqli_num_rows($products) > 0): ?>

                <?php while (
                    $product =
                    mysqli_fetch_assoc($products)
                ): ?>

                    <?php

                    $productId =
                        (int)$product["id"];

                    $stock =
                        (int)$product["stock"];

                    $isWishlisted =
                        isset(
                            $wishlistProducts[
                                $productId
                            ]
                        );

                    ?>

                    <div class="product-card">

                        <a
                            href="product_details.php?id=<?php echo $productId; ?>"
                            class="product-link"
                        >

                            <div class="product-image">

                                <?php if (
                                    !empty($product["image"])
                                ): ?>

                                    <img
                                        src="images/products/<?php echo htmlspecialchars(
                                            $product["image"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>"
                                        alt="<?php echo htmlspecialchars(
                                            $product["product_name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>"
                                    >

                                <?php else: ?>

                                    <div class="no-image">
                                        No Image
                                    </div>

                                <?php endif; ?>

                            </div>

                            <div class="product-info">

                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["product_name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </h3>

                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["description"] ?? "",
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
                                        (float)$product["price"],
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

                <?php endwhile; ?>

            <?php else: ?>

                <div class="no-products">

                    <h3>
                        No products found
                    </h3>

                    <p>
                        Try searching for another product.
                    </p>

                </div>

            <?php endif; ?>

        <?php endif; ?>

    </div>

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


<link rel="stylesheet" href="css/chat.css?v=<?php echo time(); ?>">
<script src="js/chat.js?v=<?php echo time(); ?>"></script>
<footer class="footer">
    <div class="footer-content">
        <div class="footer-brand">
            <h2>Inknest</h2>
            <p>
                Your trusted online shopping destination.
            </p>
        </div>
        <div class="footer-links">
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p>
                    <strong>Phone:</strong>
                    +977-9800000000
                </p>
                <p>
                    <strong>Email:</strong>
                    support@inknest.com
                </p>
                <p>
                    <strong>Address:</strong>
                    Kathmandu, Nepal
                </p>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>
            &copy; <?php echo date("Y"); ?> Inknest.
            All rights reserved.
        </p>
    </div>
</footer>
</body>
</html>