<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION["user_id"];
$username = $_SESSION["username"] ?? "User";

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

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
        $storedProfilePicture
    );

    if (mysqli_stmt_fetch($profileStmt)) {
        $storedProfilePicture = trim(
            $storedProfilePicture ?? ""
        );
        if ($storedProfilePicture !== "") {
            $fileName = basename(
                str_replace(
                    "\\",
                    "/",
                    $storedProfilePicture
                )
            );
            $profilePicture =
                "images/profile/" . $fileName;
        }
    }
    mysqli_stmt_close($profileStmt);
}

$productId = 0;

if (isset($_GET["id"])) {
    $productId = (int)$_GET["id"];
}

if ($productId <= 0 && isset($_POST["product_id"])) {
    $productId = (int)$_POST["product_id"];
}

if ($productId <= 0) {
    header("Location: home.php");
    exit();
}

$productStmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        product_name,
        description,
        price,
        image,
        stock
     FROM products
     WHERE id = ?
     LIMIT 1"
);

if (!$productStmt) {
    die("Unable to prepare product query.");
}

mysqli_stmt_bind_param(
    $productStmt,
    "i",
    $productId
);

if (!mysqli_stmt_execute($productStmt)) {
    mysqli_stmt_close($productStmt);
    die("Unable to load product.");
}

mysqli_stmt_bind_result(
    $productStmt,
    $loadedProductId,
    $loadedProductName,
    $loadedDescription,
    $loadedPrice,
    $loadedImage,
    $loadedStock
);

if (!mysqli_stmt_fetch($productStmt)) {
    mysqli_stmt_close($productStmt);
    header("Location: home.php");
    exit();
}

$product = [
    "id" => (int)$loadedProductId,
    "product_name" => $loadedProductName,
    "description" => $loadedDescription,
    "price" => (float)$loadedPrice,
    "image" => $loadedImage,
    "stock" => (int)$loadedStock
];

mysqli_stmt_close($productStmt);
$viewActivityStmt = mysqli_prepare(
    $conn,
    "INSERT INTO user_product_activity
    (
        user_id,
        product_id,
        activity_type,
        created_at
    )
    VALUES (?, ?, 'View', NOW())"
);

if ($viewActivityStmt) {
    mysqli_stmt_bind_param(
        $viewActivityStmt,
        "ii",
        $userId,
        $productId
    );

    mysqli_stmt_execute($viewActivityStmt);
    mysqli_stmt_close($viewActivityStmt);
}

$productImage = "";

if (!empty($product["image"])) {
    $productFileName = basename(
        str_replace(
            "\\",
            "/",
            $product["image"]
        )
    );
    $productImage ="images/products/" .
        $productFileName;
}

$averageRating = 0;
$totalReviews = 0;
$ratingStmt = mysqli_prepare(
    $conn,
    "SELECT
        COALESCE(AVG(rating), 0),
        COUNT(rating)
     FROM product_reviews
     WHERE product_id = ?
       AND parent_review_id IS NULL"
);

if ($ratingStmt) {
    mysqli_stmt_bind_param(
        $ratingStmt,
        "i",
        $productId
    );
    mysqli_stmt_execute($ratingStmt);
    mysqli_stmt_bind_result(
        $ratingStmt,
        $averageRatingValue,
        $totalReviewsValue
    );

    if (mysqli_stmt_fetch($ratingStmt)) {
        $averageRating =(float)$averageRatingValue;
        $totalReviews =(int)$totalReviewsValue;
    }
    mysqli_stmt_close($ratingStmt);
}

$userReview = null;
$userReviewStmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        rating,
        comment,
        created_at
     FROM product_reviews
     WHERE product_id = ?
       AND user_id = ?
       AND parent_review_id IS NULL
     ORDER BY created_at ASC
     LIMIT 1"
);

if ($userReviewStmt) {
    mysqli_stmt_bind_param(
        $userReviewStmt,
        "ii",
        $productId,
        $userId
    );
    mysqli_stmt_execute($userReviewStmt);
    mysqli_stmt_bind_result(
        $userReviewStmt,
        $userReviewId,
        $userReviewRating,
        $userReviewComment,
        $userReviewCreatedAt
    );

    if (mysqli_stmt_fetch($userReviewStmt)) {
        $userReview = [
            "id" => (int)$userReviewId,
            "rating" => (int)$userReviewRating,
            "comment" => $userReviewComment,
            "created_at" => $userReviewCreatedAt
        ];
    }

    mysqli_stmt_close($userReviewStmt);
}

$reviews = [];
$reviewsStmt = mysqli_prepare(
    $conn,
    "SELECT
        pr.id,
        pr.product_id,
        pr.user_id,
        pr.rating,
        pr.comment,
        pr.created_at,
        u.user_name,
        up.profile_picture
     FROM product_reviews AS pr
     INNER JOIN users AS u
        ON pr.user_id = u.id
     LEFT JOIN user_profiles AS up
        ON pr.user_id = up.user_id
     WHERE pr.product_id = ?
       AND pr.parent_review_id IS NULL
     ORDER BY pr.created_at ASC"
);

if ($reviewsStmt) {
    mysqli_stmt_bind_param(
        $reviewsStmt,
        "i",
        $productId
    );
    mysqli_stmt_execute($reviewsStmt);
    mysqli_stmt_bind_result(
        $reviewsStmt,
        $reviewId,
        $reviewProductId,
        $reviewUserId,
        $reviewRating,
        $reviewComment,
        $reviewCreatedAt,
        $reviewUserName,
        $reviewProfilePictureValue
    );

    while (mysqli_stmt_fetch($reviewsStmt)) {
        $reviews[] = [
            "id" => (int)$reviewId,
            "product_id" => (int)$reviewProductId,
            "user_id" => (int)$reviewUserId,
            "rating" => (int)$reviewRating,
            "comment" => $reviewComment,
            "created_at" => $reviewCreatedAt,
            "user_name" => $reviewUserName,
            "profile_picture" => $reviewProfilePictureValue
        ];
    }
    mysqli_stmt_close($reviewsStmt);
}

$followUpComments = [];
$commentsStmt = mysqli_prepare(
    $conn,
    "SELECT
        pr.id,
        pr.product_id,
        pr.user_id,
        pr.parent_review_id,
        pr.comment,
        pr.created_at,
        u.user_name,
        up.profile_picture
     FROM product_reviews AS pr
     INNER JOIN users AS u
        ON pr.user_id = u.id
     LEFT JOIN user_profiles AS up
        ON pr.user_id = up.user_id
     WHERE pr.product_id = ?
       AND pr.parent_review_id IS NOT NULL
     ORDER BY pr.created_at ASC"
);

if ($commentsStmt) {
    mysqli_stmt_bind_param(
        $commentsStmt,
        "i",
        $productId
    );
    mysqli_stmt_execute($commentsStmt);
    mysqli_stmt_bind_result(
        $commentsStmt,
        $commentId,
        $commentProductId,
        $commentUserId,
        $commentParentId,
        $commentText,
        $commentCreatedAt,
        $commentUserName,
        $commentProfilePictureValue
    );

    while (mysqli_stmt_fetch($commentsStmt)) {
        $parentId = (int)$commentParentId;
        if (!isset($followUpComments[$parentId])) {
            $followUpComments[$parentId] = [];
        }

        $followUpComments[$parentId][] = [
            "id" => (int)$commentId,
            "product_id" => (int)$commentProductId,
            "user_id" => (int)$commentUserId,
            "parent_review_id" => $parentId,
            "comment" => $commentText,
            "created_at" => $commentCreatedAt,
            "user_name" => $commentUserName,
            "profile_picture" => $commentProfilePictureValue
        ];
    }
    mysqli_stmt_close($commentsStmt);
}

$displayRating = (int)round($averageRating);

$profileInitial = strtoupper(
    substr(
        trim($username),
        0,
        1
    )
);

if ($profileInitial === "") {
    $profileInitial = "U";
}

$isWishlisted = false;

$wishlistStmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM wishlist
     WHERE user_id = ?
       AND product_id = ?
     LIMIT 1"
);

if ($wishlistStmt) {
    mysqli_stmt_bind_param(
        $wishlistStmt,
        "ii",
        $userId,
        $productId
    );

    mysqli_stmt_execute($wishlistStmt);
    mysqli_stmt_bind_result(
        $wishlistStmt,
        $wishlistId
    );

    if (mysqli_stmt_fetch($wishlistStmt)) {
        $isWishlisted = true;
    }

    mysqli_stmt_close($wishlistStmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>
    <?= e($product["product_name"]) ?> | Inknest
</title>
<link rel="stylesheet" href="css/product_details.css?v=<?php echo time(); ?>">
</head>

<body>
<nav class="navbar">
<a href="home.php" class="logo">
    Inknest
</a>
<div class="nav-right">
    <div class="profile">
        <?php if (!empty($profilePicture)): ?>
            <img src="<?= e($profilePicture) ?>" alt="Profile" class="profile-img">
        <?php else: ?>
            <div class="profile-initial">
                <?= e($profileInitial) ?>
            </div>
        <?php endif; ?>
        <span>
            <?= e($username) ?>
        </span>
    </div>
    <a href="home.php" class="nav-link">
        Products
    </a>
    <a href="cart.php" class="cart-link">
        Cart
        <span id="cartCount">
            0
        </span>
    </a>
    <a href="logout.php"
        class="logout">
        Logout
    </a>
</div>
</nav>

<main class="product-page">
<section class="product-section">
    <div class="product-image-container">
        <?php if (!empty($productImage)): ?>
            <img src="<?= e($productImage) ?>" alt="<?= e($product["product_name"]) ?>" class="product-image">
        <?php else: ?>
            <div class="no-product-image">
                No Image
            </div>
        <?php endif; ?>
    </div>

    <div class="product-info">
        <h1>
            <?= e($product["product_name"]) ?>
        </h1>
        <div class="product-rating">
            <span class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span
                        class="star <?= $i <= $displayRating ? "filled" : "" ?>"
                    >
                        ★
                    </span>
                <?php endfor; ?>
            </span>

            <span class="rating-number">
                <?= number_format($averageRating, 1) ?>
            </span>
            <span class="review-count">
                (<?= $totalReviews ?> reviews)
            </span>
        </div>

        <div class="product-price">
            Rs.
            <?= number_format(
                (float)$product["price"],
                2
            ) ?>
        </div>

        <div class="product-description">
            <?= nl2br(
                e($product["description"])
            ) ?>
        </div>

        <div class="stock-info">
            <?php if ((int)$product["stock"] > 0): ?>
                <span class="in-stock">
                    In Stock
                </span>

                <span>
                    <?= (int)$product["stock"] ?>
                    available
                </span>

            <?php else: ?>

                <span class="out-stock">
                    Out of Stock
                </span>

            <?php endif; ?>

        </div>

        <div class="wishlist-action">

            <button
                type="button"
                id="wishlistBtn"
                class="wishlist-btn <?= $isWishlisted ? "active" : "" ?>"
                data-product-id="<?= $productId ?>"
            >
                <?= $isWishlisted
                    ? "♥ Wishlisted"
                    : "♡ Wishlist" ?>
            </button>

            <div
                id="wishlistMessage"
                class="wishlist-message"
            ></div>

        </div>

        <?php if ((int)$product["stock"] > 0): ?>

            <div class="quantity-section">

                <label for="quantity">
                    Quantity
                </label>

                <div class="quantity-control">

                    <button
                        type="button"
                        id="decreaseQty"
                    >
                        −
                    </button>

                    <input
                        type="number"
                        id="quantity"
                        value="1"
                        min="1"
                        max="<?= (int)$product["stock"] ?>"
                    >

                    <button
                        type="button"
                        id="increaseQty"
                    >
                        +
                    </button>

                </div>

            </div>

            <div class="cart-action">

                <button
                    type="button"
                    id="addToCartBtn"
                    class="add-to-cart-btn"
                    data-product-id="<?= $productId ?>"
                >
                    Add to Cart
                </button>

                <div
                    id="cartMessage"
                    class="cart-message"
                ></div>

            </div>

        <?php else: ?>

            <div class="cart-action">

                <button
                    type="button"
                    class="add-to-cart-btn disabled"
                    disabled
                >
                    Out of Stock
                </button>

            </div>

        <?php endif; ?>

    </div>

</section>

<section class="review-section">

    <div class="section-title">

        <h2>
            Your Review
        </h2>

        <p>
            Share your experience with this product.
        </p>

    </div>

    <?php if ($userReview === null): ?>

        <form
            action="submit_review.php"
            method="POST"
            class="review-form"
            id="reviewForm"
        >

            <input
                type="hidden"
                name="product_id"
                value="<?= $productId ?>"
            >

            <div class="form-group">

                <label>
                    Rating
                </label>

                <div class="rating-input">

                    <?php for ($i = 5; $i >= 1; $i--): ?>

                        <input
                            type="radio"
                            name="rating"
                            value="<?= $i ?>"
                            id="star<?= $i ?>"
                            required
                        >

                        <label
                            for="star<?= $i ?>"
                            title="<?= $i ?> star<?= $i > 1 ? "s" : "" ?>"
                        >
                            ★
                        </label>

                    <?php endfor; ?>

                </div>

            </div>

            <div class="form-group">

                <label for="comment">
                    Comment
                </label>

                <textarea
                    name="comment"
                    id="comment"
                    rows="5"
                    maxlength="2000"
                    placeholder="Write your review..."
                    required
                ></textarea>

            </div>

            <button
                type="submit"
                class="submit-review-btn"
            >
                Submit Review
            </button>

        </form>

    <?php else: ?>

        <div class="your-review-card">

            <div class="your-review-header">

                <div class="review-user">

                    <?php if (!empty($profilePicture)): ?>

                        <img
                            src="<?= e($profilePicture) ?>"
                            alt="Profile"
                            class="review-avatar"
                        >

                    <?php else: ?>

                        <div class="review-avatar review-avatar-initial">
                            <?= e($profileInitial) ?>
                        </div>

                    <?php endif; ?>

                    <div>

                        <strong>
                            <?= e($username) ?>
                        </strong>

                        <div class="review-date">

                            <?= date(
                                "M d, Y",
                                strtotime(
                                    $userReview["created_at"]
                                )
                            ) ?>

                        </div>

                    </div>

                </div>

                <div class="review-stars">

                    <?php
                    $userRating =
                        (int)$userReview["rating"];
                    ?>

                    <?php for ($i = 1; $i <= 5; $i++): ?>

                        <span
                            class="<?= $i <= $userRating ? "filled" : "" ?>"
                        >
                            ★
                        </span>

                    <?php endfor; ?>

                </div>

            </div>

            <div class="your-review-text">

                <?= nl2br(
                    e(
                        $userReview["comment"]
                    )
                ) ?>

            </div>

        </div>

        <form
            id="followUpForm"
            class="followup-form"
            method="POST"
        >

            <input
                type="hidden"
                name="product_id"
                value="<?= $productId ?>"
            >

            <input
                type="hidden"
                name="review_id"
                value="<?= (int)$userReview["id"] ?>"
            >

            <div class="form-group">

                <label for="followUpComment">
                    Add Another Comment
                </label>

                <textarea
                    name="comment"
                    id="followUpComment"
                    rows="4"
                    maxlength="2000"
                    placeholder="Add more to your review..."
                    required
                ></textarea>

            </div>

            <button
                type="submit"
                class="comment-btn"
                id="followUpSubmit"
            >
                Add Comment
            </button>

            <div
                id="followUpMessage"
                class="follow-up-message"
            ></div>

        </form>

    <?php endif; ?>

</section>

<section class="feedback-section">

    <div class="section-title">

        <h2>
            Customer Feedback
        </h2>

        <p>
            See what customers are saying about this product.
        </p>

    </div>

    <?php if (empty($reviews)): ?>

        <div class="no-reviews">

            No reviews yet.
            Be the first to review this product.

        </div>

    <?php else: ?>

        <div class="reviews-list">

            <?php foreach ($reviews as $review): ?>

                <?php

                $reviewId =
                    (int)$review["id"];

                $reviewUsername =
                    $review["user_name"] ??
                    "User";

                $reviewInitial =
                    strtoupper(
                        substr(
                            trim($reviewUsername),
                            0,
                            1
                        )
                    );

                if ($reviewInitial === "") {
                    $reviewInitial = "U";
                }

                $reviewRating =
                    (int)$review["rating"];

                $reviewProfilePicture = "";

                if (
                    !empty(
                        $review["profile_picture"]
                    )
                ) {

                    $reviewProfileFile =
                        basename(
                            str_replace(
                                "\\",
                                "/",
                                $review["profile_picture"]
                            )
                        );

                    $reviewProfilePicture =
                        "images/profile/" .
                        $reviewProfileFile;
                }

                ?>

                <article class="review-card">

                    <div class="review-header">

                        <div class="review-user">

                            <?php if (!empty($reviewProfilePicture)): ?>

                                <img
                                    src="<?= e($reviewProfilePicture) ?>"
                                    alt="<?= e($reviewUsername) ?>"
                                    class="review-avatar"
                                >

                            <?php else: ?>

                                <div class="review-avatar review-avatar-initial">

                                    <?= e($reviewInitial) ?>

                                </div>

                            <?php endif; ?>

                            <div class="review-user-details">

                                <strong>
                                    <?= e($reviewUsername) ?>
                                </strong>

                                <span class="review-date">

                                    <?= date(
                                        "M d, Y",
                                        strtotime(
                                            $review["created_at"]
                                        )
                                    ) ?>

                                </span>

                            </div>

                        </div>

                        <div class="review-stars">

                            <?php for ($i = 1; $i <= 5; $i++): ?>

                                <span
                                    class="<?= $i <= $reviewRating ? "filled" : "" ?>"
                                >
                                    ★
                                </span>

                            <?php endfor; ?>

                        </div>

                    </div>

                    <div class="review-comment">

                        <?= nl2br(
                            e(
                                $review["comment"]
                            )
                        ) ?>

                    </div>

                    <?php if (
                        isset(
                            $followUpComments[$reviewId]
                        ) &&
                        !empty(
                            $followUpComments[$reviewId]
                        )
                    ): ?>

                        <div class="review-replies">

                            <div class="review-replies-title">
                                Additional comments
                            </div>

                            <?php foreach (
                                $followUpComments[$reviewId]
                                as $followUp
                            ): ?>

                                <?php

                                $commentUsername =
                                    $followUp["user_name"] ??
                                    "User";

                                $commentInitial =
                                    strtoupper(
                                        substr(
                                            trim(
                                                $commentUsername
                                            ),
                                            0,
                                            1
                                        )
                                    );

                                if ($commentInitial === "") {
                                    $commentInitial = "U";
                                }

                                $commentProfilePicture = "";

                                if (
                                    !empty(
                                        $followUp[
                                            "profile_picture"
                                        ]
                                    )
                                ) {

                                    $commentProfileFile =
                                        basename(
                                            str_replace(
                                                "\\",
                                                "/",
                                                $followUp[
                                                    "profile_picture"
                                                ]
                                            )
                                        );

                                    $commentProfilePicture =
                                        "images/profile/" .
                                        $commentProfileFile;
                                }

                                ?>

                                <div class="review-reply">

                                    <div class="review-reply-header">

                                        <?php if (!empty($commentProfilePicture)): ?>

                                            <img
                                                src="<?= e($commentProfilePicture) ?>"
                                                alt="<?= e($commentUsername) ?>"
                                                class="review-avatar small"
                                            >

                                        <?php else: ?>

                                            <div class="review-avatar small review-avatar-initial">

                                                <?= e(
                                                    $commentInitial
                                                ) ?>

                                            </div>

                                        <?php endif; ?>

                                        <div class="review-reply-user">

                                            <strong>
                                                <?= e(
                                                    $commentUsername
                                                ) ?>
                                            </strong>

                                            <span>

                                                <?= date(
                                                    "M d, Y",
                                                    strtotime(
                                                        $followUp[
                                                            "created_at"
                                                        ]
                                                    )
                                                ) ?>

                                            </span>

                                        </div>

                                    </div>

                                    <div class="review-reply-text">

                                        <?= nl2br(
                                            e(
                                                $followUp[
                                                    "comment"
                                                ]
                                            )
                                        ) ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>
```

</main>

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
        &copy;
        <?php echo date("Y"); ?>
        Inknest.
        All rights reserved.
    </p>

</div>

</footer>

<script src="js/product_details.js?v=<?php echo time(); ?>"></script>

</body>

</html>
