<?php
require "../config/config.php";

if (isset($_POST['coupon_code'], $_POST['price'])) {
    $code = $_POST['coupon_code'];
    $price = floatval($_POST['price']);

    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = :code AND expires_at > NOW() AND usage_limit > 0");
    $stmt->execute([':code' => $code]);
    $coupon = $stmt->fetch(PDO::FETCH_OBJ);

    if ($coupon && $coupon->status > 0 && $coupon->usage_limit > 0) {
        $discount = ($coupon->percentage / 100) * $price;
        $final = $price - $discount;
        echo json_encode([
            "success" => true,
            "message" => "Preview for coupoun: -{$coupon->percentage}% ($discount$ off)",
            "discount_amount" => $discount,
            "final_price" => round($final, 2)
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid or expired coupon"
        ]);
    }
}
