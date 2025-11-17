<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin    = $isLoggedIn && $_SESSION['role'] === 'admin';
$username   = $isLoggedIn ? $_SESSION['username'] : null;

$hideNavbar = basename($_SERVER['PHP_SELF']) === "index.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Airline Booking System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<?php if (!$hideNavbar): ?>
<nav class="navbar navbar-dark bg-primary px-3">
    <a class="navbar-brand" href="dashboard.php">Airline Booking System</a>

    <div>
        <?php if ($isLoggedIn): ?>
            <span class="text-white me-3">
                Halo, <?= htmlspecialchars($username) ?> (<?= $isAdmin ? "Admin" : "User" ?>)
            </span>
            <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container mt-4">
<?php endif; ?>
