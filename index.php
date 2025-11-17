<?php
session_start();
require_once 'config/koneksi.php';

// ===============================
// 1. LOGOUT TANPA logout.php
// ===============================
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$err = "";
$success = "";
$mode = isset($_GET['register']) ? "register" : "login";


// ===============================
// 2. LOGIN PROSES
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === "login") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $mysqli->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit;
        } else {
            $err = "Password salah.";
        }

    } else {
        $err = "Username tidak ditemukan.";
    }
}


// ===============================
// 3. REGISTER PROSES
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === "register") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Cek username sudah dipakai atau belum
    $check = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $exists = $check->get_result();

    if ($exists->num_rows > 0) {
        $err = "Username sudah digunakan. Silakan pilih username lain.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $ins = $mysqli->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
        $ins->bind_param("ss", $username, $hashed);

        if ($ins->execute()) {
            $success = "Registrasi berhasil! Silakan login.";
            $mode = "login";
        } else {
            $err = "Terjadi kesalahan saat registrasi.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login / Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f5f6fa;">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card shadow-sm">
                <div class="card-body">

                    <h3 class="text-center mb-3">
                        <?= $mode === "login" ? "Login" : "Register" ?>
                    </h3>

                    <?php if ($err): ?>
                        <div class="alert alert-danger"><?= $err ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <!-- ======================== -->
                    <!-- LOGIN FORM -->
                    <!-- ======================== -->
                    <?php if ($mode === "login"): ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <button class="btn btn-primary w-100">Login</button>

                            <div class="text-center mt-3">
                                Belum punya akun?
                                <a href="index.php?register=1">Daftar</a>
                            </div>
                        </form>
                    <?php endif; ?>


                    <!-- ======================== -->
                    <!-- REGISTER FORM -->
                    <!-- ======================== -->
                    <?php if ($mode === "register"): ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <button class="btn btn-success w-100">Register</button>

                            <div class="text-center mt-3">
                                Sudah punya akun?
                                <a href="index.php">Login</a>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
