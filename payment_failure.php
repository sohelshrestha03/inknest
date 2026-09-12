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

if (isset($_GET["data"]) && !empty($_GET["data"])) {
    $paymentMethod = "esewa";
    $decodedData = base64_decode(
        $_GET["data"],
        true
    );
    if ($decodedData === false) {
        $error = "Invalid eSewa payment response.";
    } else {
        $responseData = json_decode(
            $decodedData,
            true
        );
        if (!is_array($responseData)) {
            $error = "Invalid eSewa payment data.";
        } else {
            $status=$responseData["status"] ?? "";
            $totalAmount=$responseData["total_amount"] ?? "";
            $transactionUuid=$responseData["transaction_uuid"] ?? "";
            $transactionCode=$responseData["transaction_code"] ?? "";
            $productCode=$responseData["product_code"] ?? "";
            $transactionId =
                !empty($transactionCode)
                ? $transactionCode
                : $transactionUuid;


            if (empty($transactionUuid)) {
                $error="eSewa transaction ID is missing.";
            } else {
                $sql = "SELECT id, total_amount, payment_method, transaction_id
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
                        $sql = "UPDATE orders
                                SET payment_status = 'Failed',
                                    status = 'Failed'
                                WHERE id = ?
                                AND user_id = ?
                                AND payment_method = 'esewa'";
                        $updateStmt=
                            mysqli_prepare(
                                $conn,
                                $sql
                            );

                        if (!$updateStmt) {
                            $error="Unable to update the order.";
                        } else {
                            mysqli_stmt_bind_param(
                                $updateStmt,
                                "ii",
                                $orderId,
                                $userId
                            );

                            if (mysqli_stmt_execute($updateStmt)) {
                                $success = true;
                            } else {
                                $error ="Payment failed, but the order status could not be updated.";
                            }

                            mysqli_stmt_close(
                                $updateStmt
                            );
                        }
                    }
                    mysqli_stmt_close(
                        $stmt
                    );
                }
            }
        }
    }
} elseif (
    isset($_GET["method"])
    && $_GET["method"] === "cash"
    && isset($_GET["order_id"])
) {
    $paymentMethod = "cash";
    $orderId =(int) $_GET["order_id"];
    $sql = "SELECT id, total_amount, payment_method
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
            $sql = "UPDATE orders
                    SET payment_status = 'Pending',
                        status = 'Pending'
                    WHERE id = ?
                    AND user_id = ?
                    AND payment_method = 'cash'";

            $updateStmt =
                mysqli_prepare(
                    $conn,
                    $sql
                );

            if (!$updateStmt) {
                $error ="Unable to update the order.";
            } else {
                mysqli_stmt_bind_param(
                    $updateStmt,
                    "ii",
                    $orderId,
                    $userId
                );
                if (mysqli_stmt_execute($updateStmt)) {
                    $success = true;
                } else {
                    $error="Order was created, but payment status could not be updated.";

                }
                mysqli_stmt_close(
                    $updateStmt
                );
            }
        }
        mysqli_stmt_close(
            $stmt
        );
    }
} else {
    $error="No valid payment information was received.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Payment Failed | Inknest
    </title>
    <link rel="stylesheet" href="css/payment_success.css?v=<?php echo time(); ?>">
</head>


<body>
<div class="payment-container">
    <div class="icon error-icon">
        !
    </div>
    <h1>
        Payment Failed
    </h1>
    <p class="message">
        We could not complete your payment.
    </p>

    <?php if (!empty($error)): ?>
        <div class="error-message">
            <?php
            echo htmlspecialchars(
                $error
            );
            ?>
        </div>
    <?php endif; ?>

    <?php if ($orderId > 0): ?>
        <div class="order-info">
            <div class="order-row">
                <span>
                    Order ID
                </span>
                <strong>
                    #<?php
                    echo htmlspecialchars(
                        $orderId
                    );
                    ?>
                </strong>
            </div>

            <?php if (!empty($transactionId)): ?>
                <div class="order-row">
                    <span>
                        Transaction ID
                    </span>
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
                <span>
                    Payment Status
                </span>
                <strong>
                    Failed
                </strong>
            </div>
        </div>
    <?php endif; ?>

    <div class="buttons">
        <a href="checkout.php" class="button primary">
            Back to Checkout
        </a>
        <a href="cart.php" class="button secondary">
            Back to Cart
        </a>
    </div>
</div>
</body>
</html>