<?php
require 'includes/header.php';
require 'config/config.php';

// Base query for active products
$sql = "SELECT p.* FROM products p WHERE p.status = 1";
$params = [];

// Search filter
if (!empty($_GET['search'])) {
    $sql .= " AND p.name LIKE :search";
    $params[':search'] = '%' . trim($_GET['search']) . '%';
}

// Language filter
if (!empty($_GET['language'])) {
    $sql .= " AND p.language LIKE :language";
    $params[':language'] = '%' . trim($_GET['language']) . '%';
}

// Price range filter
if (!empty($_GET['price_range'])) {
    [$min, $max] = explode('-', $_GET['price_range']);
    $sql .= " AND p.price BETWEEN :min_price AND :max_price";
    $params[':min_price'] = $min;
    $params[':max_price'] = $max;
}

// Rating filter
if (!empty($_GET['rating'])) {
    $sql .= " AND (
        SELECT AVG(r.rating)
        FROM ratings r
        WHERE r.book_id = p.id
    ) >= :rating";
    $params[':rating'] = (int) $_GET['rating'];
}

// Prepare and execute
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<div class="container mt-4">
  <!-- Filter Form -->
  <form id="filter-form" class="mb-4" method="get" action="index.php">
    <div class="row g-3 align-items-end">

      <div class="col-md-4">
        <label class="form-label"><i class="fas fa-search me-1"></i>Search</label>
        <input
          type="text"
          name="search"
          class="form-control rounded-pill px-3"
          placeholder="Search title or author"
          value="<?php echo htmlspecialchars($_GET['search'] ?? '') ?>"
        />
      </div>

      <div class="col-md-2">
        <label class="form-label"><i class="fas fa-dollar-sign me-1"></i>Price</label>
        <select class="form-select rounded-pill px-3" name="price_range">
          <option value="">All</option>
          <option value="0-10" <?php if(($_GET['price_range'] ?? '') === '0-10') echo 'selected'; ?>>Under $10</option>
          <option value="10-30" <?php if(($_GET['price_range'] ?? '') === '10-30') echo 'selected'; ?>>$10 - $30</option>
          <option value="30-50" <?php if(($_GET['price_range'] ?? '') === '30-50') echo 'selected'; ?>>$30 - $50</option>
          <option value="50-100" <?php if(($_GET['price_range'] ?? '') === '50-100') echo 'selected'; ?>>$50 - $100</option>
          <option value="100-9999" <?php if(($_GET['price_range'] ?? '') === '100-9999') echo 'selected'; ?>>Over $100</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label"><i class="fas fa-language me-1"></i>Language</label>
        <input
          type="text"
          name="language"
          class="form-control rounded-pill px-3"
          placeholder="Language (e.g. English)"
          value="<?php echo htmlspecialchars($_GET['language'] ?? '') ?>"
        />
      </div>

      <div class="col-md-2">
        <label class="form-label"><i class="fas fa-star me-1"></i>Min Rating</label>
        <select class="form-select rounded-pill px-3" name="rating">
          <option value="">All</option>
          <option value="1" <?php if(($_GET['rating'] ?? '') === '1') echo 'selected'; ?>>1★ & up</option>
          <option value="2" <?php if(($_GET['rating'] ?? '') === '2') echo 'selected'; ?>>2★ & up</option>
          <option value="3" <?php if(($_GET['rating'] ?? '') === '3') echo 'selected'; ?>>3★ & up</option>
          <option value="4" <?php if(($_GET['rating'] ?? '') === '4') echo 'selected'; ?>>4★ & up</option>
          <option value="5" <?php if(($_GET['rating'] ?? '') === '5') echo 'selected'; ?>>5★ only</option>
        </select>
      </div>

      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-primary rounded-pill px-3">
          <i class="fas fa-filter me-1"></i> Apply
        </button>
      </div>

    </div>
  </form>

  <!-- Product Grid -->
  <div class="row mt-5">
    <?php if (count($products) > 0): ?>
      <?php foreach ($products as $product): ?>
        <div class="col-lg-4 col-md-6 col-sm-10 offset-md-0 offset-sm-1">
          <div class="card">
            <img height="213px" class="card-img-top" src="<?php echo IMGURL . '/' . htmlspecialchars($product->image); ?>" alt="Product image">
            <div class="card-body">
              <h5><b><?php echo htmlspecialchars($product->name); ?></b></h5>
              <div class="text-muted"><?php echo htmlspecialchars($product->price); ?>$</div>
              <p><?php echo substr(htmlspecialchars($product->description), 0, 120); ?></p>

              <!-- Fetch and display average rating -->
              <?php
              $avgStmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM ratings WHERE book_id = :book_id");
              $avgStmt->execute([':book_id' => $product->id]);
              $avgResult = $avgStmt->fetch(PDO::FETCH_ASSOC);
              $avg_rating = $avgResult['avg_rating'] ?? 0;

              $fullStars = floor($avg_rating);
              $halfStar = ($avg_rating - $fullStars) >= 0.5;
              $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

              for ($i = 0; $i < $fullStars; $i++) echo '<i class="fas fa-star text-warning"></i>';
              if ($halfStar) echo '<i class="fas fa-star-half-alt text-warning"></i>';
              for ($i = 0; $i < $emptyStars; $i++) echo '<i class="far fa-star text-warning"></i>';

              echo " (" . number_format($avg_rating, 1) . ")";
              ?>

              <a href="<?php echo APPURL . '/shopping/single.php?id=' . $product->id . '&' . http_build_query($_GET); ?>" class="btn btn-primary w-100 rounded my-2">
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
</div>

<?php require 'includes/footer.php'; ?>
