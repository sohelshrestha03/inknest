<?php
function getRecommendedProducts(
    mysqli $conn,
    int $userId,
    int $limit = 8,
    string $search = ""
): array {
    $products = [];
    $productSql = "
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
          AND is_deleted = 0
    ";
    $productResult = mysqli_query(
        $conn,
        $productSql
    );
    if (!$productResult) {
        return [];
    }
    while ($row = mysqli_fetch_assoc($productResult)) {
        $productId = (int) $row["id"];
        $products[$productId] = [
            "id" => $productId,
            "product_name" => $row["product_name"] ?? "",
            "category" => $row["category"] ?? "",
            "description" => $row["description"] ?? "",
            "price" => (float) ($row["price"] ?? 0),
            "image" => $row["image"] ?? "",
            "stock" => (int) ($row["stock"] ?? 0),
            "rating_score" => 0,
            "buying_score" => 0,
            "activity_score" => 0,
            "wishlist_score" => 0,
            "content_score" => 0,
            "popularity_score" => 0,
            "search_score" => 0,
            "final_score" => 0
        ];
    }
    mysqli_free_result($productResult);

    if (empty($products)) {
        return [];
    }
    $normalizeText = function (string $text): array {
        $text = strtolower($text);
        $text = preg_replace(
            '/[^a-z0-9\s]/i',
            ' ',
            $text
        );
        $words = preg_split(
            '/\s+/',
            trim($text)
        );
        $stopWords = [
            "the",
            "and",
            "for",
            "with",
            "from",
            "this",
            "that",
            "are",
            "was",
            "were",
            "has",
            "have",
            "your",
            "you",
            "our",
            "into",
            "about",
            "very",
            "more",
            "less",
            "product",
            "products"
        ];
        $result = [];
        foreach ($words as $word) {
            if (strlen($word) >= 2 &&!in_array(
                    $word,
                    $stopWords,
                    true
                )
            ) {
                $result[$word] = true;
            }
        }
        return array_keys($result);
    };
    $calculateSimilarity = function (
        array $a,
        array $b
    ): float {
        if (empty($a) || empty($b)) {
            return 0;
        }
        $a = array_unique($a);
        $b = array_unique($b);
        $intersection = count(
            array_intersect(
                $a,
                $b
            )
        );
        $union = count(
            array_unique(
                array_merge(
                    $a,
                    $b
                )
            )
        );
        if ($union === 0) {
            return 0;
        }
        return $intersection / $union;
    };
    $ratingSql = "
        SELECT
            product_id,
            COUNT(*) AS review_count,
            AVG(rating) AS average_rating
        FROM product_reviews
        GROUP BY product_id
    ";
    $ratingResult = mysqli_query(
        $conn,
        $ratingSql
    );
    $globalRating = 0;
    $globalRatingSql = mysqli_query(
        $conn,
        "
        SELECT AVG(rating) AS global_rating
        FROM product_reviews
        "
    );
    if ($globalRatingSql) {
        $globalRow = mysqli_fetch_assoc(
            $globalRatingSql
        );
        $globalRating = (float) (
            $globalRow["global_rating"] ?? 0
        );
        mysqli_free_result(
            $globalRatingSql
        );
    }
    if ($globalRating <= 0) {
        $globalRating = 3;
    }
    $minimumReviews = 3;
    if ($ratingResult) {
        while ($row = mysqli_fetch_assoc($ratingResult)) {
            $productId = (int) $row["product_id"];
            if (!isset($products[$productId])) {
                continue;
            }
            $reviewCount = (int) ($row["review_count"] ?? 0);
            $averageRating = (float) (
                $row["average_rating"] ?? 0
            );
            $bayesianRating =
                (
                    (
                        $reviewCount /
                        (
                            $reviewCount +
                            $minimumReviews
                        )
                    )
                    * $averageRating
                )
                +
                (
                    (
                        $minimumReviews /
                        (
                            $reviewCount +
                            $minimumReviews
                        )
                    )
                    * $globalRating
                );
            $score = (
                $bayesianRating - 1
            ) / 4;
            $products[$productId]["rating_score"] =
                max(
                    0,
                    min(
                        1,
                        $score
                    )
                );
        }
        mysqli_free_result(
            $ratingResult
        );
    }
    $categoryPurchases = [];
    $purchasedProducts = [];
    $purchaseSql = "
        SELECT
            oi.product_id,
            oi.quantity,
            p.category
        FROM order_items oi
        INNER JOIN orders o
            ON oi.order_id = o.id
        INNER JOIN products p
            ON oi.product_id = p.id
        WHERE o.user_id = ?
          AND o.status <> 'Cancelled'
    ";
    $purchaseStmt = mysqli_prepare(
        $conn,
        $purchaseSql
    );
    if ($purchaseStmt) {
        mysqli_stmt_bind_param(
            $purchaseStmt,
            "i",
            $userId
        );
        mysqli_stmt_execute(
            $purchaseStmt
        );
        $purchaseResult =mysqli_stmt_get_result($purchaseStmt);
        if ($purchaseResult) {
            while ($row = mysqli_fetch_assoc($purchaseResult)) {
                $productId = (int) (
                    $row["product_id"] ?? 0
                );
                $category = strtolower(
                    trim(
                        $row["category"] ?? ""
                    )
                );
                $quantity = max(
                    1,
                    (int) (
                        $row["quantity"] ?? 0
                    )
                );
                if ($category !== "") {
                    if (!isset($categoryPurchases[$category])) {
                        $categoryPurchases[
                            $category
                        ] = 0;
                    }
                    $categoryPurchases[
                        $category
                    ] += $quantity;
                }
                if (!isset($purchasedProducts[$productId])) {
                    $purchasedProducts[
                        $productId
                    ] = 0;
                }
                $purchasedProducts[
                    $productId
                ] += $quantity;
            }
            mysqli_free_result(
                $purchaseResult
            );
        }
        mysqli_stmt_close(
            $purchaseStmt
        );
    }
    $maxCategoryPurchase=!empty($categoryPurchases)
            ? max($categoryPurchases)
            : 1;
    foreach ($products as $productId => &$product) {
        $category = strtolower(
            trim(
                $product["category"]
            )
        );
        $categoryScore = 0;
        if ($category !== "" &&
            isset(
                $categoryPurchases[
                    $category
                ]
            )
        ) {
            $categoryScore=$categoryPurchases[
                    $category
                ] / $maxCategoryPurchase;
        }
        $sameProductPurchase =
            isset(
                $purchasedProducts[
                    $productId
                ]
            )
                ? min(
                    1,
                    $purchasedProducts[
                        $productId
                    ] / 5
                )
                : 0;
        $product["buying_score"] =
            min(
                1,
                (
                    $categoryScore * 0.85
                )
                +
                (
                    $sameProductPurchase * 0.15
                )
            );
    }
    unset($product);
    $activityScores = [];
    $activitySql = "
        SELECT
            product_id,
            activity_type,
            created_at
        FROM user_product_activity
        WHERE user_id = ?
    ";
    $activityStmt = mysqli_prepare(
        $conn,
        $activitySql
    );
    $activityWeights = [
        "Purchase" => 10,
        "Checkout" => 8,
        "Added to Cart" => 7,
        "Like" => 6,
        "View" => 2,
        "Remove from Cart" => -3,
        "Unlike" => -4,
        "purchase" => 10,
        "checkout" => 8,
        "added to cart" => 7,
        "like" => 6,
        "view" => 2,
        "remove from cart" => -3,
        "unlike" => -4
    ];
    if ($activityStmt) {
        mysqli_stmt_bind_param(
            $activityStmt,
            "i",
            $userId
        );
        mysqli_stmt_execute(
            $activityStmt
        );
        $activityResult =
            mysqli_stmt_get_result(
                $activityStmt
            );
        if ($activityResult) {
            while ($row = mysqli_fetch_assoc($activityResult)) {
                $productId = (int) (
                    $row["product_id"] ?? 0
                );
                if (!isset($products[$productId])) {
                    continue;
                }
                $activityType =trim($row["activity_type"] ?? "");
                $weight =
                    $activityWeights[
                        $activityType
                    ] ?? 0;
                $createdAt=$row["created_at"] ?? "";
                $daysAgo = 999;
                if (!empty($createdAt)) {
                    $timestamp=strtotime($createdAt);
                    if ($timestamp !== false) {
                        $daysAgo = floor(
                            (
                                time() -
                                $timestamp
                            ) / 86400
                        );
                    }
                }

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

                if (
                    !isset(
                        $activityScores[
                            $productId
                        ]
                    )
                ) {
                    $activityScores[
                        $productId
                    ] = 0;
                }

                $activityScores[
                    $productId
                ] += $weight * $decay;
            }

            mysqli_free_result(
                $activityResult
            );
        }

        mysqli_stmt_close(
            $activityStmt
        );
    }

    $maxPositiveActivity = 1;

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

    foreach (
        $products
        as $productId => &$product
    ) {

        $activity =
            $activityScores[
                $productId
            ] ?? 0;

        if ($activity > 0) {

            $product["activity_score"] =
                min(
                    1,
                    $activity /
                    $maxPositiveActivity
                );

        } else {

            $product["activity_score"] = 0;
        }
    }

    unset($product);

    $wishlistSql = "
        SELECT product_id
        FROM wishlist
        WHERE user_id = ?
    ";

    $wishlistStmt = mysqli_prepare(
        $conn,
        $wishlistSql
    );

    $wishlistProducts = [];

    if ($wishlistStmt) {

        mysqli_stmt_bind_param(
            $wishlistStmt,
            "i",
            $userId
        );

        mysqli_stmt_execute(
            $wishlistStmt
        );

        $wishlistResult =
            mysqli_stmt_get_result(
                $wishlistStmt
            );

        if ($wishlistResult) {

            while (
                $row = mysqli_fetch_assoc(
                    $wishlistResult
                )
            ) {

                $productId = (int) (
                    $row["product_id"] ?? 0
                );

                $wishlistProducts[
                    $productId
                ] = true;
            }

            mysqli_free_result(
                $wishlistResult
            );
        }

        mysqli_stmt_close(
            $wishlistStmt
        );
    }

    foreach (
        $products
        as $productId => &$product
    ) {

        if (
            isset(
                $wishlistProducts[
                    $productId
                ]
            )
        ) {
            $product["wishlist_score"] = 1;
        } else {
            $product["wishlist_score"] = 0;
        }
    }

    unset($product);

    $userProfileWords = [];

    foreach (
        $products
        as $productId => $product
    ) {

        $hasUserSignal =
            isset(
                $activityScores[
                    $productId
                ]
            )
            ||
            isset(
                $purchasedProducts[
                    $productId
                ]
            )
            ||
            isset(
                $wishlistProducts[
                    $productId
                ]
            );

        if (!$hasUserSignal) {
            continue;
        }

        $text =
            $product["product_name"]
            . " "
            . $product["category"]
            . " "
            . $product["description"];

        $words =
            $normalizeText($text);

        foreach ($words as $word) {
            $userProfileWords[
                $word
            ] = true;
        }
    }

    $userProfileWords =
        array_keys(
            $userProfileWords
        );

    $contentScores = [];

    foreach (
        $products
        as $productId => $product
    ) {

        $productText =
            $product["product_name"]
            . " "
            . $product["category"]
            . " "
            . $product["description"];

        $productWords =
            $normalizeText(
                $productText
            );

        $contentScores[
            $productId
        ] = $calculateSimilarity(
            $userProfileWords,
            $productWords
        );
    }

    $maxContentScore =
        !empty($contentScores)
            ? max($contentScores)
            : 0;

    foreach (
        $products
        as $productId => &$product
    ) {

        $score =
            $contentScores[
                $productId
            ] ?? 0;

        if ($maxContentScore > 0) {

            $score =
                $score /
                $maxContentScore;
        }

        $product["content_score"] =
            max(
                0,
                min(
                    1,
                    $score
                )
            );
    }

    unset($product);

    $popularity = [];

    $popularitySql = "
        SELECT
            oi.product_id,
            SUM(oi.quantity) AS purchase_count
        FROM order_items oi
        INNER JOIN orders o
            ON oi.order_id = o.id
        WHERE o.status <> 'Cancelled'
        GROUP BY oi.product_id
    ";

    $popularityResult =
        mysqli_query(
            $conn,
            $popularitySql
        );

    if ($popularityResult) {

        while (
            $row = mysqli_fetch_assoc(
                $popularityResult
            )
        ) {

            $productId = (int) (
                $row["product_id"] ?? 0
            );

            $popularity[
                $productId
            ] = (int) (
                $row["purchase_count"] ?? 0
            );
        }

        mysqli_free_result(
            $popularityResult
        );
    }

    $maxPopularity =
        !empty($popularity)
            ? max($popularity)
            : 1;

    foreach (
        $products
        as $productId => &$product
    ) {

        $purchaseCount =
            $popularity[
                $productId
            ] ?? 0;

        $product["popularity_score"] =
            $maxPopularity > 0
                ? min(
                    1,
                    $purchaseCount /
                    $maxPopularity
                )
                : 0;
    }

    unset($product);

    $searchWords =
        $normalizeText($search);

    foreach (
        $products
        as $productId => &$product
    ) {

        if (empty($searchWords)) {

            $product["search_score"] = 0;

            continue;
        }

        $nameWords =
            $normalizeText(
                $product["product_name"]
            );

        $categoryWords =
            $normalizeText(
                $product["category"]
            );

        $descriptionWords =
            $normalizeText(
                $product["description"]
            );

        $nameMatches = count(
            array_intersect(
                $searchWords,
                $nameWords
            )
        );

        $categoryMatches = count(
            array_intersect(
                $searchWords,
                $categoryWords
            )
        );

        $descriptionMatches = count(
            array_intersect(
                $searchWords,
                $descriptionWords
            )
        );

        $searchCount =
            count($searchWords);

        if ($searchCount > 0) {

            $nameScore =
                $nameMatches /
                $searchCount;

            $categoryScore =
                $categoryMatches /
                $searchCount;

            $descriptionScore =
                $descriptionMatches /
                $searchCount;

            $searchScore =
                ($nameScore * 0.60)
                +
                ($categoryScore * 0.25)
                +
                ($descriptionScore * 0.15);

            $fullText =
                strtolower(
                    $product["product_name"]
                    . " "
                    . $product["category"]
                    . " "
                    . $product["description"]
                );

            $searchLower =
                strtolower(
                    trim($search)
                );

            if (
                $searchLower !== "" &&
                strpos(
                    $fullText,
                    $searchLower
                ) !== false
            ) {

                $searchScore =
                    min(
                        1,
                        $searchScore + 0.25
                    );
            }

            $product["search_score"] =
                max(
                    0,
                    min(
                        1,
                        $searchScore
                    )
                );

        } else {

            $product["search_score"] = 0;
        }
    }

    unset($product);

    foreach (
        $products
        as $productId => &$product
    ) {

        $product["final_score"] =
            ($product["rating_score"] * 0.25)
            +
            ($product["buying_score"] * 0.20)
            +
            ($product["activity_score"] * 0.15)
            +
            ($product["wishlist_score"] * 0.10)
            +
            ($product["content_score"] * 0.15)
            +
            ($product["popularity_score"] * 0.10)
            +
            ($product["search_score"] * 0.05);
    }

    unset($product);

    foreach (
        $products
        as $productId => $product
    ) {

        if (
            isset(
                $purchasedProducts[
                    $productId
                ]
            )
        ) {
            unset(
                $products[
                    $productId
                ]
            );
        }
    }

    if (empty($products)) {
        return [];
    }

    usort(
        $products,
        function (
            $a,
            $b
        ) {

            if (
                $a["final_score"] !==
                $b["final_score"]
            ) {

                return
                    $b["final_score"]
                    <=>
                    $a["final_score"];
            }

            if (
                $a["content_score"] !==
                $b["content_score"]
            ) {

                return
                    $b["content_score"]
                    <=>
                    $a["content_score"];
            }

            if (
                $a["search_score"] !==
                $b["search_score"]
            ) {

                return
                    $b["search_score"]
                    <=>
                    $a["search_score"];
            }

            if (
                $a["popularity_score"] !==
                $b["popularity_score"]
            ) {

                return
                    $b["popularity_score"]
                    <=>
                    $a["popularity_score"];
            }

            if (
                $a["activity_score"] !==
                $b["activity_score"]
            ) {

                return
                    $b["activity_score"]
                    <=>
                    $a["activity_score"];
            }

            if (
                $a["rating_score"] !==
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

    $products =
        array_slice(
            $products,
            0,
            20
        );

    $seed =
        crc32(
            $userId
            . "-"
            . date("Y-m-d")
        );

    mt_srand($seed);

    $finalProducts = [];

    while (
        !empty($products) &&
        count($finalProducts) < $limit
    ) {

        $bestScore =
            $products[0]["final_score"];

        $eligible = [];

        foreach (
            $products
            as $index => $product
        ) {

            if (
                abs(
                    $product["final_score"]
                    -
                    $bestScore
                ) <= 0.10
            ) {

                $eligible[] = $index;

            } else {

                break;
            }
        }

        if (empty($eligible)) {
            $eligible[] = 0;
        }

        $selectedIndex =
            $eligible[
                mt_rand(
                    0,
                    count($eligible) - 1
                )
            ];

        $finalProducts[] =
            $products[
                $selectedIndex
            ];

        array_splice(
            $products,
            $selectedIndex,
            1
        );
    }
    return $finalProducts;
}
?>