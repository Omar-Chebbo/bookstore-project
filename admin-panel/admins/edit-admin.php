<?php
require "../layouts/header.php";
require "../../config/config.php";

// Check if user is logged in and POST data exists
if (!isset($_SESSION['admin_id'], $_SESSION['admin_role']) || !isset($_POST['update_admin'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$currentUserId = $_SESSION['admin_id'];
$currentUserRole = $_SESSION['admin_role'];

$admin_id = $_POST['admin_id'] ?? null;
$adminname = trim($_POST['adminname'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? '';

if (!$admin_id || !$adminname || !$email || !$role) {
    echo "<script>alert('All fields are required');window.history.back();</script>";
    exit();
}

try {
    // Admin #1 restrictions
    if ($admin_id == 1) {
        // Admin #1 can edit himself but cannot change role to Employee
        if ($currentUserId != 1) {
            echo "<script>alert('You cannot edit Admin #1');window.history.back();</script>";
            exit();
        }
        if ($role !== 'Manager') {
            $role = 'Manager'; // Force role to Manager
        }
    } else {
        // Other admins cannot edit Admin #1
        if ($currentUserRole !== 'Manager' && $currentUserId != $admin_id) {
            echo "<script>alert('You do not have permission to edit this admin');window.history.back();</script>";
            exit();
        }
    }

    // Update admin details
    $stmt = $conn->prepare("UPDATE admins SET adminname = :adminname, email = :email, role = :role WHERE id = :id");
    $stmt->execute([
        ':adminname' => $adminname,
        ':email' => $email,
        ':role' => $role,
        ':id' => $admin_id
    ]);

    // If the current user edited their own profile, update session values
    if ($admin_id == $currentUserId) {
        $_SESSION['adminname'] = $adminname;
        $_SESSION['admin_role'] = $role;
        if ($role === 'Manager' ) {
            // Redirect to home if new role is Manager
            header("Location: " . ADMINURL);
            exit();
        }
    }

    header("Location: admins.php");
    exit();

} catch (PDOException $e) {
    echo "<script>alert('Database error: " . $e->getMessage() . "');window.history.back();</script>";
}
?>
