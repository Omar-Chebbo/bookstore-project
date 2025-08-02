<?php require "../includes/header.php"; ?>
<?php require "../config/config.php"; ?>
<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: " . APPURL . "/");
    exit();
}

//  Prevent direct access to the file
$allow_direct_access = basename(__FILE__) === 'cart.php';
if (!$allow_direct_access && $_SERVER['REQUEST_METHOD'] === 'GET' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die('Direct access is not allowed.');
}

//  Fetch cart items for the current user
$products = $conn->query("SELECT * FROM cart WHERE user_id='{$_SESSION['user_id']}'");
$products->execute();
$allProducts = $products->fetchAll(PDO::FETCH_OBJ);

//  On form submit, reduce usage_limit of coupon and go to checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $price = $_POST['final_price'];
    $_SESSION['price'] = $price;

    // If coupon was applied, reduce its usage
    if (!empty($_POST['coupon_applied'])) {
        $code = $_POST['coupon_applied'];

        $stmt = $conn->prepare("UPDATE coupons SET usage_limit = usage_limit - 1 WHERE code = :code AND usage_limit > 0");
        $stmt->execute([':code' => $code]);

        $_SESSION['coupon_code'] = $code;
    }

    // Redirect to checkout (Stripe)
    header("Location: checkout.php");
    exit;
}
?>

<div class="row d-flex justify-content-center align-items-center h-100 mt-5">
    <div class="col-12">
        <div class="card card-registration card-registration-2" style="border-radius: 15px;">
            <div class="card-body p-0">
                <div class="row g-0">
                    <!--  Cart Products Section -->
                    <div class="col-lg-8">
                        <div class="p-5">
                            <div class="d-flex justify-content-between align-items-center mb-5">
                                <h1 class="fw-bold mb-0 text-black">Shopping Cart</h1>
                            </div>

                            <table class="table" height="190">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total Price</th>
                                        <th>Update</th>
                                        <th><button class="delete-all btn btn-danger text-white">Clear</button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($allProducts) > 0): ?>
                                        <?php foreach ($allProducts as $product): ?>
                                            <tr class="mb-4">
                                                <td><img width="100" height="100" src="<?= IMGURL . '/' . $product->pro_image; ?>" class="img-fluid rounded-3" alt="Product image"></td>
                                                <td><?= $product->pro_name; ?></td>
                                                <td class="pro_price"><?= $product->pro_price; ?></td>
                                                <td><input min="1" name="quantity" value="<?= $product->pro_amount; ?>" type="number" class="form-control form-control-sm pro_amount" /></td>
                                                <td class="total_price"><?= $product->pro_amount * $product->pro_price; ?></td>
                                                <td><button value="<?= $product->id; ?>" class="btn-update btn btn-warning text-white"><i class="fas fa-pen"></i></button></td>
                                                <td><button value="<?= $product->id; ?>" class="btn btn-danger text-white btn-delete"><i class="fas fa-trash-alt"></i></button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="alert alert-danger bg-danger text-white">There are no products in the cart</div>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <a href="<?= APPURL; ?>" class="btn btn-success text-white"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                        </div>
                    </div>

                    
                    <div class="col-lg-4 bg-grey">
                        <div class="p-5">
                            <h3 class="fw-bold mb-3 mt-2 pt-1">Summary</h3>
                            <hr class="my-4">

                            <div class="coupon_response"></div>

                            <form method="POST" action="cart.php">
                                <div class="mb-3">
                                    <label class="form-label">Have a discount code?</label>
                                    <input type="text" name="coupon_code" id="coupon_input" class="form-control" placeholder="Enter code (optional)">
                                    <button type="button" class="btn btn-sm btn-info mt-2 text-white apply_coupon_btn">Apply</button>
                                </div>

                                <div class="d-flex justify-content-between mb-3">
                                    <h5>Total price</h5>
                                    <h5 class="full_price">0$</h5>
                                    <input class="inp_price" name="price" type="hidden">
                                </div>

                                <div class="d-flex justify-content-between mb-3">
                                    <h5>After discount</h5>
                                    <h5 class="final_price">0$</h5>
                                </div>

                                
                                <input type="hidden" name="final_price" class="final_price_input">
                                <input type="hidden" name="coupon_applied" class="coupon_applied_input">

                                <button type="submit" name="submit" class="checkout btn btn-dark btn-block btn-lg" data-mdb-ripple-color="dark">Checkout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>


<script>
$(document).ready(function () {
    //  Recalculate prices every 2 seconds
    function calculateTotal() {
        let sum = 0;
        $(".total_price").each(function () {
            sum += parseFloat($(this).text());
        });

        $(".full_price").html(sum + "$");
        $(".inp_price").val(sum);

        let final = sum;

        // If discount is shown, apply it
        const discount = parseFloat($(".final_price").attr("data-discount")) || 0;
        final -= discount;

        $(".final_price").html(final.toFixed(2) + "$");
        $(".final_price_input").val(final.toFixed(2));

        // Show/hide checkout button
        $(".checkout").toggle(sum > 0);
    }

    setInterval(calculateTotal, 2000);

    //  Delete item
    $(".btn-delete").on("click", function () {
        const id = $(this).val();
        $.post("delete-item.php", { delete: "delete", id }, function () {
            location.reload();
        });
    });

    //  Clear all cart
    $(".delete-all").on("click", function () {
        $.post("delete-all-item.php", { delete: "delete" }, function () {
            location.reload();
        });
    });

    // Update quantity
    $(".btn-update").on("click", function () {
        const $row = $(this).closest("tr");
        const id = $(this).val();
        const pro_amount = $row.find(".pro_amount").val();

        $.post("update-item.php", { update: "update", id, pro_amount }, function () {
            location.reload();
        });
    });

    //  Apply coupon button clicked
    $(".apply_coupon_btn").on("click", function () {
        const code = $("#coupon_input").val().trim();
        const price = parseFloat($(".inp_price").val());

        if (!code || price <= 0) return;

        // Call AJAX to check coupon
        $.post("apply-coupon.php", { coupon_code: code, price: price }, function (res) {
            const data = JSON.parse(res);

            if (data.success) {
                $(".coupon_response").html(`<div class='alert alert-success'>${data.message}</div>`);
                $(".final_price").html(data.final_price + "$");
                $(".final_price").attr("data-discount", data.discount_amount);
                $(".final_price_input").val(data.final_price);
                $(".coupon_applied_input").val(code);
            } else {
    $(".coupon_response").html(`<div class='alert alert-danger'>${data.message}</div>`);
    
    //  Remove previous discount completely
    $(".final_price").html(price.toFixed(2) + "$");
    $(".final_price").removeAttr("data-discount");
    $(".final_price_input").val(price.toFixed(2));
    $(".coupon_applied_input").val("");
}
        });
    });
});
</script>
