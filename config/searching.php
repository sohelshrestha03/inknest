<?php
function searchProducts(
    mysqli $conn,
    string $search,
    string $category,
    string $orderBy,
    int $limit = 50
): array {
    $products = [];
    $search = trim($search);
    $category = trim($category);
    $limit = max(1, min($limit, 100));
    if ($search === "" && $category === "") {
        $sql = "
            SELECT
                id,
                product_name,
                category,
                description,
                price,
                image,
                stock
            FROM products
            WHERE is_deleted = 0
            ORDER BY $orderBy
            LIMIT $limit
        ";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $products[] = $row;
            }
            mysqli_free_result($result);
        }
        return $products;
    }
    $conditions = [
        "p.is_deleted = 0"
    ];
    $params = [];
    $types = "";
    if ($category !== "") {
        $conditions[] = "
            LOWER(TRIM(p.category)) = LOWER(TRIM(?))
        ";
        $params[] = $category;
        $types .= "s";
    }
    if ($search !== "") {
        $conditions[] = "
            (
                p.product_name LIKE ?
                OR p.category LIKE ?
                OR p.description LIKE ?
            )
        ";
        $searchTerm = "%" . $search . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }
    $where = implode(" AND ", $conditions);
    $relevance = "";
    if ($search !== "") {
        $exactName = $search;
        $startName = $search . "%";
        $containsName = "%" . $search . "%";
        $containsCategory = "%" . $search . "%";
        $containsDescription = "%" . $search . "%";
        $relevance = "
            CASE
                WHEN LOWER(p.product_name) = LOWER(?) THEN 1
                WHEN LOWER(p.product_name) LIKE LOWER(?) THEN 2
                WHEN LOWER(p.product_name) LIKE LOWER(?) THEN 3
                WHEN LOWER(p.category) LIKE LOWER(?) THEN 4
                WHEN LOWER(p.description) LIKE LOWER(?) THEN 5
                ELSE 6
            END,
        ";
        $params = [
            $exactName,
            $startName,
            $containsName,
            $containsCategory,
            $containsDescription,
            ...$params
        ];
        $types = "sssss" . $types;
    }
    $sql = "
        SELECT
            p.id,
            p.product_name,
            p.category,
            p.description,
            p.price,
            p.image,
            p.stock
        FROM products p
        WHERE $where
        ORDER BY
            $relevance
            $orderBy
        LIMIT $limit
    ";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [];
    }
    if (!empty($params)) {
        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $products;
}
?>