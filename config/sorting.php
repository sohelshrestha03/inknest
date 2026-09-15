<?php
$sort = $_GET["sort"] ?? "newest";
$allowedSorts = [
    "newest" => "id DESC",
    "oldest" => "id ASC",
    "price_low" => "price ASC",
    "price_high" => "price DESC",
    "name_az" => "product_name ASC",
    "name_za" => "product_name DESC"
];
if (!isset($allowedSorts[$sort])) {
    $sort = "newest";
}
$orderBy = $allowedSorts[$sort];
function sortProducts(array &$products, string $sort): void
{
    usort($products, function ($a, $b) use ($sort) {
        switch ($sort) {
            case "oldest":
                return ((int)($a["id"] ?? 0))
                    <=> ((int)($b["id"] ?? 0));
            case "price_low":
                return ((float)($a["price"] ?? 0))
                    <=> ((float)($b["price"] ?? 0));
            case "price_high":
                return ((float)($b["price"] ?? 0))
                    <=> ((float)($a["price"] ?? 0));
            case "name_az":
                return strcasecmp(
                    $a["product_name"] ?? "",
                    $b["product_name"] ?? ""
                );
            case "name_za":
                return strcasecmp(
                    $b["product_name"] ?? "",
                    $a["product_name"] ?? ""
                );
            case "newest":
            default:
                return ((int)($b["id"] ?? 0))
                    <=> ((int)($a["id"] ?? 0));
        }
    });
}
?>