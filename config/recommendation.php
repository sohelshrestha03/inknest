<?php

function getRecommendedProducts(
    mysqli $conn,
    int $userId,
    int $limit = 8
): array {

    $limit = max(1, min($limit, 20));

    $products = [];

    $productQuery = mysqli_query(
        $conn,
        "
        SELECT
            id,
            product_name,
            category,
            description,
            price,
            image,
            stock
        FROM products
        WHERE stock > 0
        "
    );

    if (!$productQuery) {
        return [];
    }

    while ($product = mysqli_fetch_assoc($productQuery)) {

        $productId = (int)$product["id"];

        $products[$productId] = [
            "id" => $productId,
            "product_name" => $product["product_name"],
            "category" => $product["category"],
            "description" => $product["description"],
            "price" => (float)$product["price"],
            "image" => $product["image"],
            "stock" => (int)$product["stock"],
            "rating_score" => 0,
            "buying_score" => 0,
            "activity_score" => 0,
            "wishlist_score" => 0,
            "final_score" => 0
        ];
    }

    if (empty($products)) {
        return [];
    }

    $ratingData = [];

    $ratingQuery = mysqli_query(
        $conn,
        "
        SELECT
            product_id,
            COUNT(rating) AS rating_count,
            AVG(rating) AS average_rating
        FROM product_reviews
        WHERE rating IS NOT NULL
        GROUP BY product_id
        "
    );

    $globalRatingSum = 0;
    $globalRatingCount = 0;

    if ($ratingQuery) {

        while ($rating = mysqli_fetch_assoc($ratingQuery)) {

            $productId = (int)$rating["product_id"];
            $count = (int)$rating["rating_count"];
            $average = (float)$rating["average_rating"];

            $ratingData[$productId] = [
                "count" => $count,
                "average" => $average
            ];

            $globalRatingSum += $average * $count;
            $globalRatingCount += $count;
        }
    }

    $globalAverage =
        $globalRatingCount > 0
            ? $globalRatingSum / $globalRatingCount
            : 3.0;

    $minimumReviews = 3;

    foreach ($products as $productId => &$product) {

        $count =
            $ratingData[$productId]["count"] ?? 0;

        $average =
            $ratingData[$productId]["average"] ?? 0;

        if ($count > 0) {

            $bayesianRating =
                (
                    ($count / ($count + $minimumReviews))
                    * $average
                )
                +
                (
                    ($minimumReviews / ($count + $minimumReviews))
                    * $globalAverage
                );

        } else {

            $bayesianRating =
                $globalAverage;
        }

        $product["rating_score"] =
            max(
                0,
                min(
                    1,
                    ($bayesianRating - 1) / 4
                )
            );
    }

    unset($product);

    $categoryPurchases = [];
    $purchasedProducts = [];

    $purchaseStmt = mysqli_prepare(
        $conn,
        "
        SELECT
            oi.product_id,
            oi.quantity,
            p.category
        FROM order_items AS oi
        INNER JOIN orders AS o
            ON oi.order_id = o.id
        INNER JOIN products AS p
            ON oi.product_id = p.id
        WHERE o.user_id = ?
          AND o.status <> 'Cancelled'
        "
    );

    if ($purchaseStmt) {

        mysqli_stmt_bind_param(
            $purchaseStmt,
            "i",
            $userId
        );

        if (
            mysqli_stmt_execute(
                $purchaseStmt
            )
        ) {

            mysqli_stmt_bind_result(
                $purchaseStmt,
                $purchaseProductId,
                $purchaseQuantity,
                $purchaseCategory
            );

            while (
                mysqli_stmt_fetch(
                    $purchaseStmt
                )
            ) {

                $purchaseProductId =
                    (int)$purchaseProductId;

                $purchaseQuantity =
                    max(
                        1,
                        (int)$purchaseQuantity
                    );

                $purchaseCategory =
                    trim(
                        (string)$purchaseCategory
                    );

                $purchasedProducts[
                    $purchaseProductId
                ] = true;

                if (
                    !isset(
                        $categoryPurchases[
                            $purchaseCategory
                        ]
                    )
                ) {
                    $categoryPurchases[
                        $purchaseCategory
                    ] = 0;
                }

                $categoryPurchases[
                    $purchaseCategory
                ] += $purchaseQuantity;
            }
        }

        mysqli_stmt_close(
            $purchaseStmt
        );
    }

    $maxCategoryPurchase =
        !empty($categoryPurchases)
            ? max($categoryPurchases)
            : 0;

    foreach ($products as $productId => &$product) {

        $category =
            trim(
                (string)$product["category"]
            );

        $categoryScore = 0;

        if (
            $maxCategoryPurchase > 0 &&
            isset(
                $categoryPurchases[$category]
            )
        ) {

            $categoryScore =
                $categoryPurchases[$category]
                /
                $maxCategoryPurchase;
        }

        $sameProductPurchase =
            isset(
                $purchasedProducts[$productId]
            )
                ? 1
                : 0;

        $product["buying_score"] =
            min(
                1,
                (
                    ($categoryScore * 0.85)
                    +
                    ($sameProductPurchase * 0.15)
                )
            );
    }

    unset($product);

    $activityScores = [];

    $activityStmt = mysqli_prepare(
        $conn,
        "
        SELECT
            product_id,
            activity_type,
            created_at
        FROM user_product_activity
        WHERE user_id = ?
          AND product_id IS NOT NULL
          AND activity_type IN
          (
              'View',
              'Added to Cart',
              'Checkout',
              'Purchase',
              'Remove from Cart',
              'Like',
              'Unlike'
          )
        ORDER BY created_at DESC
        "
    );

    if ($activityStmt) {

        mysqli_stmt_bind_param(
            $activityStmt,
            "i",
            $userId
        );

        if (
            mysqli_stmt_execute(
                $activityStmt
            )
        ) {

            mysqli_stmt_bind_result(
                $activityStmt,
                $activityProductId,
                $activityType,
                $createdAt
            );

            while (
                mysqli_stmt_fetch(
                    $activityStmt
                )
            ) {

                $activityProductId =
                    (int)$activityProductId;

                $activityType =
                    trim(
                        (string)$activityType
                    );

                $createdAt =
                    (string)$createdAt;

                $baseWeight = 0;

                if (
                    $activityType === "Purchase"
                ) {

                    $baseWeight = 10;

                } elseif (
                    $activityType === "Checkout"
                ) {

                    $baseWeight = 8;

                } elseif (
                    $activityType === "Added to Cart"
                ) {

                    $baseWeight = 7;

                } elseif (
                    $activityType === "Like"
                ) {

                    $baseWeight = 6;

                } elseif (
                    $activityType === "View"
                ) {

                    $baseWeight = 2;

                } elseif (
                    $activityType === "Remove from Cart"
                ) {

                    $baseWeight = -3;

                } elseif (
                    $activityType === "Unlike"
                ) {

                    $baseWeight = -4;
                }

                if ($baseWeight === 0) {
                    continue;
                }

                $activityTime =
                    strtotime($createdAt);

                if ($activityTime === false) {
                    continue;
                }

                $daysAgo =
                    max(
                        0,
                        (
                            time() - $activityTime
                        ) / 86400
                    );

                if ($daysAgo <= 1) {

                    $decay = 1.00;

                } elseif ($daysAgo <= 7) {

                    $decay = 0.80;

                } elseif ($daysAgo <= 30) {

                    $decay = 0.50;

                } elseif ($daysAgo <= 90) {

                    $decay = 0.20;

                } else {

                    $decay = 0.05;
                }

                $score =
                    $baseWeight * $decay;

                if (
                    !isset(
                        $activityScores[
                            $activityProductId
                        ]
                    )
                ) {

                    $activityScores[
                        $activityProductId
                    ] = 0;
                }

                $activityScores[
                    $activityProductId
                ] += $score;
            }
        }

        mysqli_stmt_close(
            $activityStmt
        );
    }

    $maxPositiveActivity = 0;

    foreach (
        $activityScores as $score
    ) {

        if (
            $score >
            $maxPositiveActivity
        ) {

            $maxPositiveActivity =
                $score;
        }
    }

    foreach ($products as $productId => &$product) {

        if (
            $maxPositiveActivity > 0 &&
            isset(
                $activityScores[$productId]
            )
        ) {

            $normalizedActivity =
                $activityScores[$productId]
                /
                $maxPositiveActivity;

            $product["activity_score"] =
                max(
                    0,
                    min(
                        1,
                        $normalizedActivity
                    )
                );

        } else {

            $product["activity_score"] = 0;
        }
    }

    unset($product);

    $wishlistProducts = [];

    $wishlistStmt = mysqli_prepare(
        $conn,
        "
        SELECT
            product_id
        FROM wishlist
        WHERE user_id = ?
        "
    );

    if ($wishlistStmt) {

        mysqli_stmt_bind_param(
            $wishlistStmt,
            "i",
            $userId
        );

        if (
            mysqli_stmt_execute(
                $wishlistStmt
            )
        ) {

            mysqli_stmt_bind_result(
                $wishlistStmt,
                $wishlistProductId
            );

            while (
                mysqli_stmt_fetch(
                    $wishlistStmt
                )
            ) {

                $wishlistProducts[
                    (int)$wishlistProductId
                ] = true;
            }
        }

        mysqli_stmt_close(
            $wishlistStmt
        );
    }

    foreach ($products as $productId => &$product) {

        $product["wishlist_score"] =
            isset(
                $wishlistProducts[$productId]
            )
                ? 1
                : 0;

        $product["final_score"] =
            (
                $product["rating_score"]
                * 0.35
            )
            +
            (
                $product["buying_score"]
                * 0.30
            )
            +
            (
                $product["activity_score"]
                * 0.20
            )
            +
            (
                $product["wishlist_score"]
                * 0.15
            );
    }

    unset($product);

    usort(
        $products,
        function ($a, $b) {

            if (
                $a["final_score"] !=
                $b["final_score"]
            ) {

                return
                    $b["final_score"]
                    <=>
                    $a["final_score"];
            }

            if (
                $a["activity_score"] !=
                $b["activity_score"]
            ) {

                return
                    $b["activity_score"]
                    <=>
                    $a["activity_score"];
            }

            if (
                $a["rating_score"] !=
                $b["rating_score"]
            ) {

                return
                    $b["rating_score"]
                    <=>
                    $a["rating_score"];
            }

            return strcmp(
                $a["product_name"],
                $b["product_name"]
            );
        }
    );

    $candidateProducts = [];

    foreach ($products as $product) {

        $productId =
            (int)$product["id"];

        if (
            isset(
                $purchasedProducts[
                    $productId
                ]
            )
        ) {
            continue;
        }

        $candidateProducts[] =
            $product;

        if (
            count($candidateProducts)
            >= 20
        ) {
            break;
        }
    }

    if (
        count($candidateProducts)
        < $limit
    ) {

        foreach ($products as $product) {

            $alreadyExists = false;

            foreach (
                $candidateProducts
                as $candidate
            ) {

                if (
                    (int)$candidate["id"]
                    ===
                    (int)$product["id"]
                ) {

                    $alreadyExists = true;
                    break;
                }
            }

            if ($alreadyExists) {
                continue;
            }

            $candidateProducts[] =
                $product;

            if (
                count($candidateProducts)
                >= 20
            ) {
                break;
            }
        }
    }

    if (empty($candidateProducts)) {
        return [];
    }

    $seed =
        crc32(
            $userId .
            "-" .
            date("Y-m-d")
        );

    mt_srand($seed);

    usort(
        $candidateProducts,
        function ($a, $b) {

            $scoreDifference =
                $b["final_score"]
                -
                $a["final_score"];

            if (
                abs($scoreDifference)
                > 0.10
            ) {

                return
                    $scoreDifference > 0
                        ? -1
                        : 1;
            }

            return mt_rand(
                -1,
                1
            );
        }
    );

    mt_srand();

    return array_slice(
        $candidateProducts,
        0,
        $limit
    );
}
?>