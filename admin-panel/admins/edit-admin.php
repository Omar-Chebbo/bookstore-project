<?php
require "../layouts/header.php";
require "../../config/config.php";

// Ensure session and form submission
if (!isset($_SESSION['admin_id'], $_SESSION['admin_role']) || !isset($_POST['update_admin'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$currentUserId = $_SESSION['admin_id'];
$currentUserRole = $_SESSION['admin_role'];

$admin_id = $_POST['admin_id'] ?? null;
$adminname = trim($_POST['adminname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = $_POST['role'] ?? '';

if (!$admin_id || !$adminname || !$email || !$role) {
    echo "<script>alert('All fields are required');window.history.back();</script>";
    exit();
}

try {
    // Get the current admin data including status
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id = :id");
    $stmt->execute([':id' => $admin_id]);
    $adminData = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$adminData) {
        echo "<script>alert('Admin not found');window.history.back();</script>";
        exit();
    }

    // Prevent editing if admin is revoked
    if ($adminData->status == 0) {
        echo "<script>alert('You cannot edit a revoked admin');window.history.back();</script>";
        exit();
    }

    // Admin #1 restrictions
    if ($admin_id == 1) {
        if ($currentUserId != 1) {
            echo "<script>alert('You cannot edit Admin #1');window.history.back();</script>";
            exit();
        }
        $role = 'Manager'; // force role to Manager
    } else {
        // Only Manager or self can edit
        if ($currentUserRole !== 'Manager' && $currentUserId != $admin_id) {
            echo "<script>alert('You do not have permission to edit this admin');window.history.back();</script>";
            exit();
        }
    }

    // Build SQL query and parameters
    $sql = "UPDATE admins SET adminname = :adminname, email = :email, role = :role";
    $params = [
        ':adminname' => $adminname,
        ':email' => $email,
        ':role' => $role,
        ':id' => $admin_id
    ];

    // If password provided, hash and update
    if (!empty($password)) {
        $sql .= ", mypassword = :password";
        $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id = :id";

    // Execute update
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Update session if self-edit
    if ($admin_id == $currentUserId) {
        $_SESSION['adminname'] = $adminname;
        $_SESSION['admin_role'] = $role;

        if ($role === 'Employee') {
            // Redirect to home if new role is Employee
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
