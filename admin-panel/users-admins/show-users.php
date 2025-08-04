<?php
require "../layouts/header.php";
require "../../config/config.php";

if(!isset($_SESSION['adminname'])) {
      header("location: ".ADMINURL."/admins/login-admins.php");
    }

$select = $conn->query("SELECT * FROM users");
  $select->execute();

  $users = $select->fetchAll(PDO::FETCH_OBJ);
?>
<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
</head>

<div class="row">
  <div class="col">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4 d-inline">Users</h5>
        <a  href="<?php echo ADMINURL; ?>/users-admins/create-users.php" class="btn btn-primary mb-4 text-center float-right">Create Users</a>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Ban/Unban</th>
              <th>Comments</th>
              <th>Orders</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr id="user-<?= $user->id; ?>">
                <td><?= $user->id; ?></td>
                <td><?= htmlspecialchars($user->username); ?></td>
                <td><?= htmlspecialchars($user->email); ?></td>
                <td>
                  <button class="btn btn-sm btn-warning toggle-ban" data-id="<?= $user->id; ?>">
                    <?php if($user->is_banned){echo "unban";}
                    else echo "ban";?>
                  </button>
                </td>
                <td>
                  <button 
                    class="btn btn-info btn-sm view-comments" 
                    data-id="<?= $user->id ?>"
                    data-username="<?= htmlspecialchars($user->username) ?>"
                    >
                    View Comments
                  </button>
                </td>
                <td>
                    <button 
                        class="btn btn-primary btn-sm view-orders" 
                        data-id="<?= $user->id ?>" 
                        data-username="<?= htmlspecialchars($user->username) ?>">
                        View Orders
                    </button>
                </td>
                <td>
                  <a href="<?= ADMINURL ?>/users-admins/edit-user.php?id=<?= $user->id ?>" 
                    class="btn btn-sm btn-secondary">
                    Edit
                  </a>
                </td>
                <td>
                  <button class="btn btn-danger btn-sm delete-user" data-id="<?= $user->id; ?>">
                    Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
<div class="modal fade" id="commentsModal" tabindex="-1" role="dialog" aria-labelledby="commentsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="commentsModalLabel">User Comments</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="commentsContent">
          Loading comments...
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="ordersModal" tabindex="-1" role="dialog" aria-labelledby="ordersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ordersModalLabel">User Orders</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="ordersContent">
        Loading orders...
      </div>
    </div>
  </div>
</div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).on('click', '.toggle-ban', function () {
    const button = $(this);
    const userId = button.data('id');

    $.ajax({
        url: '<?=ADMINURL?>/users-admins/toggle-ban.php',
        method: 'POST',
        data: { id: userId },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                const banned = res.newStatus === 1;
                button.text(banned ? 'Unban' : 'Ban');
            } else {
                alert("Error: " + res.error);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
            alert("AJAX request failed.");
        }
    });
});
$(document).ready(function() {
  $('.delete-user').click(function() {
    const userId = $(this).data('id');
    if (!confirm("Are you sure you want to delete this user?")) return;

    $.ajax({
      url: '<?= ADMINURL ?>/users-admins/delete-user.php',
      method: 'POST',
      data: { id: userId },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#user-' + userId).remove(); // remove row from page
        } else {
          alert("Error deleting user.");
        }
      },
      error: function() {
        alert("Server error.");
      }
    });
  });
});

$(document).on('click', '.view-comments', function() {
  const userId = $(this).data('id');
  const username = $(this).data('username');

  // Set modal title
  $('#commentsModalLabel').text('Comments by ' + username);

  // Show modal
  $('#commentsModal').modal('show');

  // Show loading text
  $('#commentsContent').html('Loading comments...');

  // Fetch comments with AJAX
  $.ajax({
    url: '<?=ADMINURL?>/users-admins/get-users-comments.php', // Adjust path as needed
    type: 'GET',
    data: { user_id: userId },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        if (response.comments.length === 0) {
          $('#commentsContent').html('<p>No comments found for this user.</p>');
        } else {
          let html = '<ul class="list-group">';
          response.comments.forEach(comment => {
            html += `
              <li class="list-group-item">
                <small class="text-muted">${comment.created_at}</small><br>
                ${escapeHtml(comment.content)}
              </li>
            `;
          });
          html += '</ul>';
          $('#commentsContent').html(html);
        }
      } else {
        $('#commentsContent').html('<p class="text-danger">Error: ' + response.error + '</p>');
      }
    },
    error: function() {
      $('#commentsContent').html('<p class="text-danger">Failed to load comments.</p>');
    }
  });
});

$(document).on('click', '.view-orders', function() {
  const userId = $(this).data('id');
  const username = $(this).data('username');

  $('#ordersModalLabel').text('Orders by ' + username);
  $('#ordersContent').html('Loading orders...');
  $('#ordersModal').modal('show');

  $.ajax({
    url: '<?= ADMINURL ?>/users-admins/get-users-orders.php',
    method: 'GET',
    data: { user_id: userId },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        if (res.orders.length === 0) {
          $('#ordersContent').html('<p>No orders found for this user.</p>');
        } else {
          let html = '<table class="table table-bordered"><thead><tr><th>Order ID</th><th>Price</th><th>Date</th></tr></thead><tbody>';
          res.orders.forEach(order => {
            html += `<tr>
              <td>${order.id}</td>
              <td>${order.price}</td>
              <td>${order.created_at}</td>
            </tr>`;
          });
          html += '</tbody></table>';
          $('#ordersContent').html(html);
        }
      } else {
        $('#ordersContent').html('<p class="text-danger">Error: ' + res.error + '</p>');
      }
    },
    error: function() {
      $('#ordersContent').html('<p class="text-danger">Failed to load orders.</p>');
    }
  });
});

// Helper function to escape HTML
function escapeHtml(text) {
  return $('<div>').text(text).html();
}
</script>

<?php require "../layouts/footer.php"; ?>