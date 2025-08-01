<?php require "../includes/header.php"; ?>
<?php require "../config/config.php"; ?>

<?php
// Smart Back Link Logic (preserve filters if they exist)
$backLink = APPURL . '/index.php';
if (!empty($_SERVER['QUERY_STRING'])) {
    $query = $_SERVER['QUERY_STRING'];
    parse_str($query, $queryParams);
    unset($queryParams['id']);
    if (!empty($queryParams)) {
        $backLink = APPURL . '/index.php?' . http_build_query($queryParams);
    }
}

// Add to cart handling (AJAX expects POST with cart_submit)
if (isset($_POST['cart_submit'])) {
    $pro_id = $_POST['pro_id'];
    $pro_name = $_POST['pro_name'];
    $pro_image = $_POST['pro_image'];
    $pro_price = $_POST['pro_price'];
    $pro_amount = $_POST['pro_amount'];
    $pro_file = $_POST['pro_file'];
    $user_id = $_POST['user_id'];

    $insert = $conn->prepare("INSERT INTO cart(pro_id, pro_name, pro_image, pro_price, pro_amount, pro_file, user_id)
                             VALUES (:pro_id, :pro_name, :pro_image, :pro_price, :pro_amount, :pro_file, :user_id)");
    $insert->execute([
        ':pro_id' => $pro_id,
        ':pro_name' => $pro_name,
        ':pro_image' => $pro_image,
        ':pro_price' => $pro_price,
        ':pro_amount' => $pro_amount,
        ':pro_file' => $pro_file,
        ':user_id' => $user_id,
    ]);
    exit;
}

// Handle rating removal
if (isset($_POST['remove_rating']) && isset($_SESSION['user_id'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_SESSION['user_id'];

    $delete = $conn->prepare("DELETE FROM ratings WHERE book_id = :book_id AND user_id = :user_id");
    $delete->execute([':book_id' => $book_id, ':user_id' => $user_id]);

    // Redirect with filters preserved
    $redirectQuery = "id=$book_id";
    if (!empty($_POST['back_query'])) {
        $redirectQuery .= '&' . $_POST['back_query'];
    }
    header("Location: single.php?$redirectQuery");
    exit;
}

// Handle rating submission (normal POST, reloads page)
if (isset($_POST['rate_submit']) && isset($_SESSION['user_id'])) {
    $book_id = $_POST['book_id'];
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user_id'];

    // Check if user already rated this book
    $stmt = $conn->prepare("SELECT * FROM ratings WHERE book_id = :book_id AND user_id = :user_id");
    $stmt->execute([':book_id' => $book_id, ':user_id' => $user_id]);
    if ($stmt->rowCount() > 0) {
        $update = $conn->prepare("UPDATE ratings SET rating = :rating WHERE book_id = :book_id AND user_id = :user_id");
        $update->execute([':rating' => $rating, ':book_id' => $book_id, ':user_id' => $user_id]);
    } else {
        $insert = $conn->prepare("INSERT INTO ratings (book_id, user_id, rating) VALUES (:book_id, :user_id, :rating)");
        $insert->execute([':book_id' => $book_id, ':user_id' => $user_id, ':rating' => $rating]);
    }

    // Redirect with filters preserved
    $redirectQuery = "id=$book_id";
    if (!empty($_POST['back_query'])) {
        $redirectQuery .= '&' . $_POST['back_query'];
    }
    header("Location: single.php?$redirectQuery");
    exit;
}

// Get product info
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if (isset($_SESSION['user_id'])) {
        $select = $conn->prepare("SELECT * FROM cart WHERE pro_id = :pro_id AND user_id = :user_id");
        $select->execute([':pro_id' => $id, ':user_id' => $_SESSION['user_id']]);

        $select_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE pro_id = :pro_id AND user_id = :user_id");
        $select_wishlist->execute([':pro_id' => $id, ':user_id' => $_SESSION['user_id']]);
        $fetch = $select_wishlist->fetch(PDO::FETCH_OBJ);
    }

    $row = $conn->prepare("SELECT * FROM products WHERE status = 1 AND id = :id");
    $row->execute([':id' => $id]);
    $product = $row->fetch(PDO::FETCH_OBJ);

    $avg_stmt = $conn->prepare("SELECT AVG(rating) AS avg_rating FROM ratings WHERE book_id = :book_id");
    $avg_stmt->execute([':book_id' => $id]);
    $avg_rating = $avg_stmt->fetch(PDO::FETCH_OBJ)->avg_rating;

    $user_rating = 0;
    if (isset($_SESSION['user_id'])) {
        $user_rating_stmt = $conn->prepare("SELECT rating FROM ratings WHERE book_id = :book_id AND user_id = :user_id");
        $user_rating_stmt->execute([':book_id' => $id, ':user_id' => $_SESSION['user_id']]);
        $user_rating = $user_rating_stmt->fetchColumn() ?: 0;
    }
} else {
    header("Location: " . APPURL . "/404.php");
    exit;
}
?>

<div class="row d-flex justify-content-center mt-4">
    <div class="col-md-10">
        <div class="card">
            <div class="row">
                <div class="col-md-6">
                    <div class="images p-3">
                        <div class="text-center p-4">
                            <img id="main-image" src="../admin-panel/products-admins/images/<?php echo htmlspecialchars($product->image); ?>" width="250" alt="Product Image" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="product p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?php echo htmlspecialchars($backLink); ?>" class="ml-1 btn btn-primary">
                                <i class="fa fa-long-arrow-left"></i> Back
                            </a>
                            <i class="fa fa-shopping-cart text-muted"></i>
                        </div>
                        <div class="mt-4 mb-3">
                            <h5 class="text-uppercase"><?php echo htmlspecialchars($product->name); ?></h5>
                            <div class="price d-flex flex-row align-items-center">
                                <span class="act-price"><?php echo htmlspecialchars($product->price); ?> $</span>
                            </div>
                        </div>
                        <p class="about"><?php echo htmlspecialchars($product->description); ?></p>

                        <!-- Add to Cart Form -->
                        <form method="post" id="cart-form">
                            <input type="hidden" name="pro_id" value="<?php echo $product->id; ?>">
                            <input type="hidden" name="pro_name" value="<?php echo htmlspecialchars($product->name); ?>">
                            <input type="hidden" name="pro_image" value="<?php echo htmlspecialchars($product->image); ?>">
                            <input type="hidden" name="pro_price" value="<?php echo htmlspecialchars($product->price); ?>">
                            <input type="hidden" name="pro_amount" value="1">
                            <input type="hidden" name="pro_file" value="<?php echo htmlspecialchars($product->file); ?>">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                            <?php endif; ?>
                            <button id="cart-submit" name="cart_submit" type="submit" class="btn btn-primary text-uppercase mr-2 px-4">
                                <?php echo (isset($select) && $select->rowCount() > 0) ? 'Added to cart' : 'Add to cart'; ?>
                            </button>
                        </form>

                        <!-- Wishlist Buttons -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if (isset($select_wishlist) && $select_wishlist->rowCount() > 0): ?>
                                <button value="<?php echo $fetch->id; ?>" class="btn-delete-wishlist btn btn-primary text-uppercase mr-2 px-4">
                                    <i class="fas fa-heart"></i> Added to wishlist
                                </button>
                            <?php else: ?>
                                <button class="wishlist-btn btn btn-primary text-uppercase mr-2 px-4">
                                    <i class="fas fa-heart"></i> Add to wishlist
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Display average rating -->
                        <div class="mt-4">
                            <strong>Average Rating: </strong>
                            <?php
                            $fullStars = floor($avg_rating);
                            $halfStar = ($avg_rating - $fullStars) >= 0.5;
                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

                            for ($i = 0; $i < $fullStars; $i++) echo '<i class="fas fa-star text-warning"></i> ';
                            if ($halfStar) echo '<i class="fas fa-star-half-alt text-warning"></i> ';
                            for ($i = 0; $i < $emptyStars; $i++) echo '<i class="far fa-star text-warning"></i> ';
                            ?>
                            (<?php echo number_format($avg_rating, 2); ?>)
                        </div>

                        <!-- User Star Rating Form -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php
                            $originalQuery = $_SERVER['QUERY_STRING'];
                            parse_str($originalQuery, $queryParams);
                            unset($queryParams['id']);
                            $backParams = http_build_query($queryParams);
                            ?>
                            <form method="post" id="rating-form" class="mt-3">
                                <input type="hidden" name="book_id" value="<?php echo $product->id; ?>">
                                <input type="hidden" name="rating" id="star-rating-input" value="<?php echo $user_rating; ?>">
                                <input type="hidden" name="back_query" value="<?php echo htmlspecialchars($backParams); ?>">

                                <label for="rating">Your Rating:</label><br>
                                <div id="star-rating" class="mb-2" style="cursor: pointer;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-star <?php echo ($i <= $user_rating) ? 'fas text-warning' : 'far text-secondary'; ?>" data-rating="<?php echo $i; ?>"></i>
                                    <?php endfor; ?>
                                </div>

                                <button type="submit" name="rate_submit" class="btn btn-sm btn-outline-primary mt-2">Submit Rating</button>

                                <?php if ($user_rating > 0): ?>
                                    <button type="submit" name="remove_rating" class="btn btn-sm btn-outline-danger mt-2 ms-2">Remove My Rating</button>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <p class="mt-3">Please <a href="<?php echo APPURL; ?>/auth/login.php">login</a> to rate this book.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    // Add to cart AJAX submit
    $("#cart-form").on("submit", function(e) {
        e.preventDefault();
        var formdata = $(this).serialize() + '&cart_submit=cart_submit';
        $.ajax({
            type: "post",
            url: "single.php?id=<?php echo $id; ?>",
            data: formdata,
            success: function(){
                alert("Added to cart successfully");
                $("#cart-submit").html("<i class='fas fa-shopping-cart'></i> Added to cart").prop("disabled", true);
            }
        });
    });

    // Wishlist buttons AJAX
    $(".wishlist-btn").on("click", function(e) {
        e.preventDefault();
        var formdata = $("#cart-form").serialize() + '&submit=submit';
        $.ajax({
            type: "post",
            url: "wishlist.php",
            data: formdata,
            success: function() {
                alert("Added to wishlist successfully");
                $(".wishlist-btn").html("<i class='fas fa-heart'></i> Added to wishlist").addClass("btn-delete-wishlist").removeClass("wishlist-btn");
            }
        });
    });

    $(".btn-delete-wishlist").on('click', function(e) {
        e.preventDefault();
        var id = $(this).val();
        $.ajax({
            type: "POST",
            url: "delete-item-wishlist.php",
            data: { delete: "delete", id: id },
            success: function() {
                alert("Product deleted successfully from wishlist");
                $(".btn-delete-wishlist").html("<i class='fas fa-heart'></i> Add to wishlist").addClass("wishlist-btn").removeClass("btn-delete-wishlist");
            }
        });
    });

    // Star rating click behavior
    $('#star-rating .fa-star').on('click', function(){
        let rating = $(this).data('rating');
        $('#star-rating-input').val(rating);

        // Update star fill colors
        $('#star-rating .fa-star').each(function(){
            let starValue = $(this).data('rating');
            if (starValue <= rating) {
                $(this).removeClass('far text-secondary').addClass('fas text-warning');
            } else {
                $(this).removeClass('fas text-warning').addClass('far text-secondary');
            }
        });
    });

    // Prevent submitting empty rating
    $('#rating-form').on('submit', function(e){
        let rating = parseInt($('#star-rating-input').val());
        if (!rating || rating === 0) {
            e.preventDefault();
            alert('Please select a star rating before submitting.');
        }
    });
});

</script>
