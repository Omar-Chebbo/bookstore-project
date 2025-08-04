<?php
session_start();
define("ADMINURL", "https://bookstore.kesug.com/I439-Project/admin-panel");
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <!-- This file has been downloaded from Bootsnipp.com. Enjoy! -->
  <title>Admin Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles/style.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</head>

<body>
  <!-- Top header navbar -->
  <nav class="navbar header-top fixed-top navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">Admins panel</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#topNavbar" aria-controls="topNavbar"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="topNavbar">
        <ul class="navbar-nav ml-auto">
          <?php if (!isset($_SESSION['adminname'])) : ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ADMINURL; ?>/admins/login-admins.php">Login</a>
            </li>
          <?php else : ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <?php echo $_SESSION['adminname']; ?>
              </a>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownUser">
                <a class="dropdown-item" href="<?php echo ADMINURL; ?>/admins/logout-admins.php">Logout</a>
              </div>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Left vertical sidebar (separate nav) -->
  <?php if (isset($_SESSION['adminname'])) : ?>
    <nav class="side-nav bg-dark position-fixed" style="top: 56px; left: 0; width: 200px; height: calc(100% - 56px); padding-top: 1rem;">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link text-white" href="<?php echo ADMINURL; ?>">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="<?php echo ADMINURL; ?>/admins/admins.php">Admins</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="<?php echo ADMINURL; ?>/categories-admins/show-categories.php">Categories</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="<?php echo ADMINURL; ?>/products-admins/show-products.php">Products</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="<?php echo ADMINURL; ?>/users-admins/show-users.php">Users</a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>

  <!-- Page content wrapper with left margin -->
  <div id="wrapper" style="margin-left: 200px; padding: 90px 15px 15px;">
