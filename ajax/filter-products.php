<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/config.php';

$sql = "SELECT * FROM products WHERE status = 1";
$params = [];

if (!empty($_GET['search'])) {
    $sql .= " AND name LIKE :search";
    $params[':search'] = '%' . trim($_GET['search']) . '%';
}

if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $sql .= " AND price >= :min_price";
    $params[':min_price'] = floatval($_GET['min_price']);
}

if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $sql .= " AND price <= :max_price";
    $params[':max_price'] = floatval($_GET['max_price']);
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_OBJ);

// Back to Home button - now always visible
echo '<a href="' . APPURL . '" class="btn btn-secondary mb-3">&larr; Back to Home</a>';

if (count($products) === 0) {
    echo '<div class="col-12"><p>No products found matching your criteria.</p></div>';
} else {
    echo '<div class="row mt-5">';
    foreach ($products as $product) {
?>
        <div class="col-lg-4 col-md-6 col-sm-10 offset-md-0 offset-sm-1">
            <div class="card">
                <img height="213px" class="card-img-top" src="<?php echo IMGURL . '/' . htmlspecialchars($product->image); ?>">
                <div class="card-body">
                    <h5 class="d-inline"><b><?php echo htmlspecialchars($product->name); ?></b></h5>
                    <h5 class="d-inline">
                        <div class="text-muted d-inline"><?php echo htmlspecialchars($product->price); ?>$</div>
                    </h5>
                    <p><?php echo substr(htmlspecialchars($product->description), 0, 120); ?></p>
                    <a href="<?php echo APPURL . '/shopping/single.php?id=' . $product->id . '&' . http_build_query($_GET); ?>" class="btn btn-primary w-100 rounded my-2">
                        More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <br>
        </div>
<?php
    }
    echo '</div>';
}
?>