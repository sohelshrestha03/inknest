<?php
session_start();
include "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];
$success = false;
$error = "";
$paymentMethod = "";
$orderId = 0;
$transactionId = null;
$esewaProductCode = "EPAYTEST";
$esewaSecretKey = "8gBm/:&EnhH.1/q";

function updateOrderPayment(
    $conn,
    $orderId,
    $paymentStatus,
    $transactionId = null
) {
    $sql = "UPDATE orders
            SET payment_status = ?, transaction_id = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $paymentStatus,
        $transactionId,
        $orderId
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

function recordPurchaseActivity($conn, $userId, $orderId)
{
    $sql = "SELECT DISTINCT product_id
            FROM order_items
            WHERE order_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(
            "Purchase activity SELECT prepare failed: "
            . mysqli_error($conn)
        );
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $orderId
    );

    if (!mysqli_stmt_execute($stmt)) {
        error_log(
            "Purchase activity SELECT execute failed: "
            . mysqli_stmt_error($stmt)
        );

        mysqli_stmt_close($stmt);
        return false;
    }

    $result = mysqli_stmt_get_result($stmt);

    $activitySql = "INSERT INTO user_product_activity
                    (user_id, product_id, activity_type)
                    VALUES (?, ?, ?)";

    $activityStmt = mysqli_prepare(
        $conn,
        $activitySql
    );

    if (!$activityStmt) {
        error_log(
            "Purchase activity INSERT prepare failed: "
            . mysqli_error($conn)
        );

        mysqli_stmt_close($stmt);
        return false;
    }

    $activityType = "Purchase";

    while ($row = mysqli_fetch_assoc($result)) {

        $productId = (int) $row["product_id"];

        mysqli_stmt_bind_param(
            $activityStmt,
            "iis",
            $userId,
            $productId,
            $activityType
        );

        if (!mysqli_stmt_execute($activityStmt)) {
            error_log(
                "Purchase activity INSERT failed: "
                . mysqli_stmt_error($activityStmt)
            );

            mysqli_stmt_close($activityStmt);
            mysqli_stmt_close($stmt);

            return false;
        }
    }
    mysqli_stmt_close($activityStmt);
    mysqli_stmt_close($stmt);
    return true;
}


if (isset($_GET["data"])
    &&
    !empty($_GET["data"])) {
    $paymentMethod = "esewa";
    $decodedData = base64_decode(
        $_GET["data"],
        true
    );

    if ($decodedData === false) {
        $error="Invalid eSewa payment response.";
    } else {
        $responseData = json_decode(
            $decodedData,
            true
        );

        if (!is_array($responseData)) {
            $error ="Invalid eSewa payment data.";
        } else {
            $status=$responseData["status"] ?? "";
            $totalAmount=$responseData["total_amount"] ?? "";
            $transactionUuid=$responseData["transaction_uuid"] ?? "";
            $productCode=$responseData["product_code"] ?? "";
            $transactionCode=$responseData["transaction_code"] ?? "";
            $signedFieldNames=$responseData["signed_field_names"] ?? "";
            $signature=$responseData["signature"] ?? "";

            if (empty($transactionUuid)) {
                $error ="eSewa transaction ID is missing.";
            } elseif ($productCode !== $esewaProductCode) {
                $error="Invalid eSewa product code.";
            } elseif (empty($signedFieldNames)) {
                $error="eSewa signature information is missing.";
            } elseif (empty($signature)) {
                $error ="eSewa signature is missing.";
            } else {
                $sql =
                    "SELECT id, total_amount, payment_method, transaction_id
                     FROM orders
                     WHERE user_id = ?
                     AND payment_method = 'esewa'
                     AND transaction_id = ?
                     LIMIT 1";

                $stmt = mysqli_prepare(
                    $conn,
                    $sql
                );

                if (!$stmt) {
                    $error="Unable to verify the order.";
                } else {
                    mysqli_stmt_bind_param(
                        $stmt,
                        "is",
                        $userId,
                        $transactionUuid
                    );
                    mysqli_stmt_execute($stmt);
                    $result=mysqli_stmt_get_result($stmt);

                    if (mysqli_num_rows($result) !== 1) {
                        $error="eSewa order could not be found.";
                    } else {
                        $order=mysqli_fetch_assoc($result);
                        $orderId=(int) $order["id"];
                        $orderAmount=(float) $order["total_amount"];
                        $receivedAmount=(float) $totalAmount;
                        $amountMatches=abs(
                                $receivedAmount
                                -
                                $orderAmount
                            ) < 0.01;

                        if (!$amountMatches) {
                            $error="Payment amount does not match the order amount.";
                        } else {
                            $fieldNames =
                                explode(
                                    ",",
                                    $signedFieldNames
                                );
                            $messageParts = [];

                            foreach ($fieldNames as $field) {
                                $field = trim($field);
                                if (isset($responseData[$field])) {
                                    $messageParts[] =
                                        $field
                                        . "="
                                        . $responseData[$field];
                                }
                            }

                            $message =
                                implode(
                                    ",",
                                    $messageParts
                                );

                            $expectedSignature =
                                base64_encode(
                                    hash_hmac(
                                        "sha256",
                                        $message,
                                        $esewaSecretKey,
                                        true
                                    )
                                );

                            if (!hash_equals($expectedSignature,$signature)) {
                                $error="eSewa signature verification failed.";
                            } else {
                                if ($status === "COMPLETE") {
                                    $transactionId =
                                        !empty($transactionCode)
                                        ? $transactionCode
                                        : $transactionUuid;

                                    if (updateOrderPayment(
                                            $conn,
                                            $orderId,
                                            "Paid",
                                            $transactionId
                                        )
                                    ) {
                                        $success = true;
                                    } else {
                                        $error="Payment was verified, but the order could not be updated.";
                                    }
                                } else {
                                    $error="eSewa payment was not completed. Status: "
                                        . htmlspecialchars($status);
                                }
                            }
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
} elseif (isset($_GET["method"])
    &&
    $_GET["method"] === "cash"
    &&
    isset($_GET["order_id"])
) {
    $paymentMethod = "cash";
    $orderId=(int) $_GET["order_id"];
    $sql =
        "SELECT id, total_amount, payment_method
         FROM orders
         WHERE id = ?
         AND user_id = ?
         AND payment_method = 'cash'
         LIMIT 1";

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    if (!$stmt) {
        $error="Unable to verify the order.";
    } else {
        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $orderId,
            $userId
        );
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) !== 1) {
            $error="Invalid cash order.";
        } else {
            if (updateOrderPayment(
                    $conn,
                    $orderId,
                    "Pending",
                    null
                )
            ) {
                $success = true;
            } else {
                $error="Order was created, but payment status could not be updated.";
            }
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $error="No valid payment information was received.";
}

if ($success === true && $orderId > 0) {
    $checkSql =
        "SELECT COUNT(*) AS total
         FROM user_product_activity upa
         INNER JOIN order_items oi
             ON upa.product_id = oi.product_id
         WHERE upa.user_id = ?
         AND oi.order_id = ?
         AND upa.activity_type = 'Purchase'";

    $checkStmt =
        mysqli_prepare(
            $conn,
            $checkSql
        );

    if ($checkStmt) {
        mysqli_stmt_bind_param(
            $checkStmt,
            "ii",
            $userId,
            $orderId
        );
        mysqli_stmt_execute($checkStmt);
        $checkResult =
            mysqli_stmt_get_result(
                $checkStmt
            );

        $checkRow =
            mysqli_fetch_assoc(
                $checkResult
            );

        mysqli_stmt_close(
            $checkStmt
        );

        if ((int) $checkRow["total"] === 0) {
            recordPurchaseActivity(
                $conn,
                $userId,
                $orderId
            );
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        echo $success
            ? "Payment Successful"
            : "Payment Failed";
        ?>
        | Inknest
    </title>
    <link rel="stylesheet" href="css/payment_success.css?v=<?php echo time(); ?>">
</head>

<body>
<div class="payment-container">
    <?php if ($success): ?>
        <div class="icon success-icon">
            ✓
        </div>
        <?php if ($paymentMethod === "cash"): ?>
            <h1>Order Placed Successfully</h1>
            <p class="message">
                Your order has been placed successfully.
                You can pay when your order is delivered.
            </p>
        <?php else: ?>
            <h1>Payment Successful</h1>
            <p class="message">
                Your payment has been verified successfully
                and your order has been confirmed.
            </p>
        <?php endif; ?>

        <div class="order-info">
            <div class="order-row">
                <span>Order ID</span>
                <strong>
                    #<?php echo htmlspecialchars($orderId); ?>
                </strong>
            </div>

            <div class="order-row">
                <span>Payment Method</span>
                <strong>
                    <?php
                    echo htmlspecialchars(
                        strtoupper($paymentMethod)
                    );
                    ?>
                </strong>
            </div>

            <?php if (!empty($transactionId)): ?>
                <div class="order-row">
                    <span>Transaction ID</span>
                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $transactionId
                        );
                        ?>
                    </strong>
                </div>
            <?php endif; ?>

            <div class="order-row">
                <span>Payment Status</span>
                <strong>
                    <?php
                    echo $paymentMethod === "cash"
                        ? "Pending"
                        : "Paid";
                    ?>
                </strong>
            </div>
        </div>

        <div class="buttons">
            <a href="home.php" class="button primary">Continue Shopping</a>
            <a href="profile.php" class="button secondary">My Profile</a>
        </div>

        <script>
            localStorage.removeItem("inknestCart");
        </script>
    <?php else: ?>
        <div class="icon error-icon">
            !
        </div>
        <h1>Payment Failed</h1>
        <p class="message">
            We could not confirm your payment.
        </p>
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php
                echo htmlspecialchars($error);
                ?>
            </div>
        <?php endif; ?>

        <div class="buttons">
            <a href="checkout.php"
                class="button primary">
                Back to Checkout
            </a>
            <a href="cart.php"
                class="button secondary">
                Back to Cart
            </a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>