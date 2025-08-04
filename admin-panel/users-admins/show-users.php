<?php
// show-users.php
require "../layouts/header.php";
require "../../config/config.php";

// 1. Ensure admin is logged in
session_start();
if (!isset($_SESSION['adminname'])) {
    header("location: " . ADMINURL . "/admins/login-admins.php");
    exit;
}

// 2. Fetch users (including country & birthdate)
$select = $conn->prepare("SELECT * FROM users");
$select->execute();
$users = $select->fetchAll(PDO::FETCH_OBJ);

// 3. Compute bulk coupon limit = floor(total_users / 2)
$totalUsers = count($users);
$bulkLimit = floor($totalUsers / 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">

  <!-- Header with Create + Bulk Gift buttons -->
  <div class="d-flex mb-3">
    <h3 class="mr-auto">Users</h3>
    <button id="sendAllGiftBtn" 
            class="btn btn-success mr-2" 
            data-bulk-limit="<?= $bulkLimit ?>">
      Send Gift to All
    </button>
    <a href="<?= ADMINURL ?>/users-admins/create-users.php" class="btn btn-primary">
      Create Users
    </a>
  </div>

  <!-- Users table -->
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th><th>Username</th><th>Email</th>
        <th>Country</th><th>Birthdate</th>
        <th>Ban/Unban</th><th>Comments</th>
        <th>Orders</th><th>Send Gift</th>
        <th>Edit</th><th>Delete</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
      <tr id="user-<?= $user->id ?>">
        <td><?= $user->id ?></td>
        <td><?= htmlspecialchars($user->username) ?></td>
        <td><?= htmlspecialchars($user->email) ?></td>
        <td><?= htmlspecialchars(!empty($user->Country) ? $user->Country : '–') ?></td>
        <td><?= htmlspecialchars(!empty($user->Birthdate) ? $user->Birthdate : '–') ?></td>
        <!-- Ban/Unban -->
        <td>
          <button class="btn btn-sm btn-warning toggle-ban" data-id="<?= $user->id ?>">
            <?= $user->is_banned ? 'Unban' : 'Ban' ?>
          </button>
        </td>
        <!-- View Comments -->
        <td>
          <button class="btn btn-info btn-sm view-comments"
                  data-id="<?= $user->id ?>"
                  data-username="<?= htmlspecialchars($user->username) ?>">
            View Comments
          </button>
        </td>
        <!-- View Orders -->
        <td>
          <button class="btn btn-primary btn-sm view-orders"
                  data-id="<?= $user->id ?>"
                  data-username="<?= htmlspecialchars($user->username) ?>">
            View Orders
          </button>
        </td>
        <!-- Send Gift -->
        <td>
          <button class="btn btn-success btn-sm send-gift-user"
                  data-id="<?= $user->id ?>"
                  data-username="<?= htmlspecialchars($user->username) ?>">
            Send Gift
          </button>
        </td>
        <!-- Edit -->
        <td>
          <a href="<?= ADMINURL ?>/users-admins/edit-user.php?id=<?= $user->id ?>"
             class="btn btn-secondary btn-sm">Edit</a>
        </td>
        <!-- Delete -->
        <td>
          <button class="btn btn-danger btn-sm delete-user" data-id="<?= $user->id ?>">
            Delete
          </button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modals -->

<!-- 1) Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="commentsModalLabel">User Comments</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="commentsContent">
        Loading comments...
      </div>
    </div>
  </div>
</div>

<!-- 2) Orders Modal -->
<div class="modal fade" id="ordersModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ordersModalLabel">User Orders</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="ordersContent">
        Loading orders...
      </div>
    </div>
  </div>
</div>

<!-- 3) Single User Gift Modal -->
<div class="modal fade" id="giftUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="giftUserForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            Send Gift to <span id="giftUsername"></span>
          </h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_id" id="giftUserId">
          <div class="form-group">
            <label>Usage Limit (1–2)</label>
            <input type="number" name="usage_limit" class="form-control" min="1" max="2" value="1" required>
          </div>
          <div class="form-group">
            <label>Discount % (max 10%)</label>
            <input type="number" name="percentage" class="form-control" min="1" max="10" value="10" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Send Email</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- 4) Bulk Gift Modal -->
<div class="modal fade" id="giftAllModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="giftAllForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Send Gift to All Users</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <p>Total users: <strong><?= $totalUsers ?></strong></p>
          <p>Default usage limit: <strong><?= $bulkLimit ?></strong></p>
          <input type="hidden" name="usage_limit" value="<?= $bulkLimit ?>">
          <div class="form-group">
            <label>Discount % (max 25%)</label>
            <input type="number" name="percentage" class="form-control" min="1" max="25" value="25" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Send to All</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require "../layouts/footer.php"; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
// Helper: escape HTML to prevent XSS
function escapeHtml(text) {
  return $('<div>').text(text).html();
}

// 1) Ban/Unban user
$(document).on('click', '.toggle-ban', function() {
  const btn   = $(this);
  const userId = btn.data('id');
  $.post('<?= ADMINURL ?>/users-admins/toggle-ban.php', { id: userId }, function(res) {
    if (res.success) {
      btn.text(res.newStatus ? 'Unban' : 'Ban');
    } else {
      alert('Error: ' + res.error);
    }
  }, 'json').fail(() => alert('Server error.'));
});

// 2) Delete user
$('.delete-user').on('click', function() {
  const userId = $(this).data('id');
  if (!confirm('Confirm delete?')) return;
  $.post('<?= ADMINURL ?>/users-admins/delete-user.php', { id: userId }, function(res) {
    if (res.success) {
      $('#user-' + userId).remove();
    } else {
      alert('Error: ' + res.error);
    }
  }, 'json').fail(() => alert('Server error.'));
});

// 3) View comments
$(document).on('click', '.view-comments', function() {
  const userId   = $(this).data('id'),
        username = $(this).data('username');
  $('#commentsModalLabel').text('Comments by ' + escapeHtml(username));
  $('#commentsContent').text('Loading comments...');
  $('#commentsModal').modal('show');
  $.getJSON('<?= ADMINURL ?>/users-admins/get-users-comments.php', { user_id: userId })
    .done(res => {
      if (res.success) {
        if (!res.comments.length) {
          $('#commentsContent').html('<p>No comments.</p>');
        } else {
          let html = '<ul class="list-group">';
          res.comments.forEach(c => {
            html += `<li class="list-group-item">
                       <small class="text-muted">${c.created_at}</small><br>
                       ${escapeHtml(c.content)}
                     </li>`;
          });
          html += '</ul>';
          $('#commentsContent').html(html);
        }
      } else {
        $('#commentsContent').html('<p class="text-danger">' + res.error + '</p>');
      }
    })
    .fail(() => $('#commentsContent').html('<p class="text-danger">Server error.</p>'));
});

// 4) View orders
$(document).on('click', '.view-orders', function() {
  const userId   = $(this).data('id'),
        username = $(this).data('username');
  $('#ordersModalLabel').text('Orders by ' + escapeHtml(username));
  $('#ordersContent').text('Loading orders...');
  $('#ordersModal').modal('show');
  $.getJSON('<?= ADMINURL ?>/users-admins/get-users-orders.php', { user_id: userId })
    .done(res => {
      if (res.success) {
        if (!res.orders.length) {
          $('#ordersContent').html('<p>No orders.</p>');
        } else {
          let t = '<table class="table"><thead><tr>'
                + '<th>ID</th><th>Price</th><th>Date</th></tr></thead><tbody>';
          res.orders.forEach(o => {
            t += `<tr><td>${o.id}</td><td>${o.price}</td><td>${o.created_at}</td></tr>`;
          });
          t += '</tbody></table>';
          $('#ordersContent').html(t);
        }
      } else {
        $('#ordersContent').html('<p class="text-danger">' + res.error + '</p>');
      }
    })
    .fail(() => $('#ordersContent').html('<p class="text-danger">Server error.</p>'));
});

// 5) Send gift to single user
$(document).on('click', '.send-gift-user', function() {
  const btn = $(this);
  $('#giftUserId').val(btn.data('id'));
  $('#giftUsername').text(btn.data('username'));
  $('#giftUserModal').modal('show');
});
$('#giftUserForm').on('submit', function(e) {
  e.preventDefault();
  $.post('<?= ADMINURL ?>/users-admins/send-gift-user.php', $(this).serialize(), function(res) {
    if (res.success) {
      alert('Gift sent!');
      $('#giftUserModal').modal('hide');
    } else {
      alert('Error: ' + res.error);
    }
  }, 'json').fail(() => alert('Server error.'));
});

// 6) Send gift to all users
$('#sendAllGiftBtn').on('click', function() {
  $('#giftAllModal').modal('show');
});
$('#giftAllForm').on('submit', function(e) {
  e.preventDefault();
  $.post('<?= ADMINURL ?>/users-admins/send-gift-all.php', $(this).serialize(), function(res) {
    if (res.success) {
      alert('Bulk gifts sent!');
      $('#giftAllModal').modal('hide');
    } else {
      alert('Error: ' + res.error);
    }
  }, 'json').fail(() => alert('Server error.'));
});
</script>
</body>
</html>
