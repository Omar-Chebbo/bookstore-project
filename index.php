<?php require "includes/header.php"; ?>
<?php require "config/config.php"; ?>

<!-- Filter/Search Form -->
<form id="filter-form" class="mt-4 mb-4" method="get" action="index.php">
  <div class="row g-3 align-items-center">
    <div class="col-md-4">
      <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? '') ?>" class="form-control rounded-pill px-3" placeholder="Search title or author" />
    </div>
    <div class="col-md-2">
      <input type="number" name="min_price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? '') ?>" class="form-control rounded-pill px-3" placeholder="Min price" min="0" />
    </div>
    <div class="col-md-2">
      <input type="number" name="max_price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? '') ?>" class="form-control rounded-pill px-3" placeholder="Max price" min="0" />
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary rounded-pill w-100" type="submit">Filter</button>
    </div>
  </div>
</form>

<?php
// Build query based on GET
$sql = "SELECT * FROM products WHERE status=1";
$params = [];

if (!empty($_GET['search'])) {
  $sql .= " AND name LIKE :search";
  $params[':search'] = '%' . trim($_GET['search']) . '%';
}

if (!empty($_GET['min_price']) && is_numeric($_GET['min_price'])) {
  $sql .= " AND price >= :min_price";
  $params[':min_price'] = $_GET['min_price'];
}

if (!empty($_GET['max_price']) && is_numeric($_GET['max_price'])) {
  $sql .= " AND price <= :max_price";
  $params[':max_price'] = $_GET['max_price'];
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_OBJ);

// Build current query string for "Back" link in single.php
$current_query = http_build_query($_GET);
?>

<!-- Product List Container -->
<div id="product-list" class="row mt-5">
  <?php if (count($products) > 0): ?>
    <?php foreach ($products as $product): ?>
      <div class="col-lg-4 col-md-6 col-sm-10 offset-md-0 offset-sm-1">
        <div class="card">
          <img height="213px" class="card-img-top" src="<?php echo IMGURL . '/' . htmlspecialchars($product->image); ?>">
          <div class="card-body">
            <h5 class="d-inline"><b><?php echo htmlspecialchars($product->name); ?></b></h5>
            <h5 class="d-inline">
              <div class="text-muted d-inline"><?php echo htmlspecialchars($product->price); ?>$</div>
            </h5>
            <p><?php echo substr(htmlspecialchars($product->description), 0, 120); ?></p>
<a href="<?php echo APPURL . '/shopping/single.php?id=' . $product->id . '&' . $current_query; ?>" class="btn btn-primary w-100 rounded my-2">
  More <i class="fas fa-arrow-right"></i>
</a>

          </div>
        </div>
        <br>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-12">
      <p class="text-muted">No products found matching your criteria.</p>
    </div>
  <?php endif; ?>
</div>

<?php require "includes/footer.php"; ?>
