<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$userId = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: history.php");
    exit();
}

$orderId = isset($_POST["order_id"])
    ? (int) $_POST["order_id"]
    : 0;

if ($orderId <= 0) {
    header("Location: history.php?error=invalid_order");
    exit();
}
mysqli_begin_transaction($conn);
try {
    $sql = "SELECT
                id,
                status,
                payment_status
            FROM orders
            WHERE id = ?
            AND user_id = ?
            FOR UPDATE";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare order query.");
    }
    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $orderId,
        $userId
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$order) {
        throw new Exception("Order not found.");
    }

    $currentStatus = strtolower(
        trim($order["status"] ?? "")
    );

    if ($currentStatus !== "pending" &&
        $currentStatus !== "processing"
    ) {
        throw new Exception(
            "This order cannot be cancelled."
        );
    }

    $sql = "SELECT
                product_id,
                quantity
            FROM order_items
            WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception(
            "Failed to prepare order items query."
        );
    }
    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $orderId
    );

    mysqli_stmt_execute($stmt);
    $itemsResult = mysqli_stmt_get_result($stmt);
    $items = [];
    while ($item = mysqli_fetch_assoc($itemsResult)) {
        $items[] = $item;
    }
    mysqli_stmt_close($stmt);

    foreach ($items as $item) {
        $productId = (int) $item["product_id"];
        $quantity = (int) $item["quantity"];

        if ($productId <= 0 || $quantity <= 0) {
            continue;
        }

        $sql = "UPDATE products
                SET stock = stock + ?
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            throw new Exception(
                "Failed to update product stock."
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $quantity,
            $productId
        );

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception(
                "Failed to restore product stock."
            );
        }
        mysqli_stmt_close($stmt);
    }

    $newStatus = "Cancelled";
    $sql = "UPDATE orders
            SET status = ?
            WHERE id = ?
            AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception(
            "Failed to prepare cancellation query."
        );
    }
    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $newStatus,
        $orderId,
        $userId
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception(
            "Failed to cancel order."
        );
    }
    mysqli_stmt_close($stmt);
    $paymentStatus = strtolower(
        trim($order["payment_status"] ?? "")
    );
  
    if ($paymentStatus === "pending" ||
        $paymentStatus === "unpaid") {
        $newPaymentStatus = "Cancelled";
        $sql = "UPDATE orders
                SET payment_status = ?
                WHERE id = ?
                AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            throw new Exception(
                "Failed to prepare payment status query."
            );
        }
        mysqli_stmt_bind_param(
            $stmt,
            "sii",
            $newPaymentStatus,
            $orderId,
            $userId
        );

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception(
                "Failed to update payment status."
            );
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($conn);
    header("Location: history.php?success=cancelled");
    exit();
} catch (Exception $e) {
    mysqli_rollback($conn);
    header(
        "Location: history.php?error=" .
        urlencode($e->getMessage())
    );
    exit();
}
?>