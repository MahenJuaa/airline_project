<?php
require_once 'includes/header.php';
require_once 'config/koneksi.php';
require_once 'includes/functions.php';
require_admin();

$err = "";
$success = "";

// ========== JIKA FORM DI-SUBMIT ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $flight_code = $_POST['flight_code'];
    $origin = $_POST['origin'];
    $destination = $_POST['destination'];
    $depart_time = $_POST['depart_time'];
    $price = $_POST['price'];
    $seats = $_POST['seats'];

    $stmt = $mysqli->prepare("
        INSERT INTO flights (flight_code, origin, destination, depart_time, price, seats)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssssii",
        $flight_code,
        $origin,
        $destination,
        $depart_time,
        $price,
        $seats
    );

    if ($stmt->execute()) {
        $success = "Penerbangan berhasil ditambahkan!";
        header("refresh:1.5; url=dashboard.php");
    } else {
        $err = "Gagal menyimpan data: " . $mysqli->error;
    }
}
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-3">Tambah Penerbangan</h4>

            <!-- Alert -->
            <?php if ($err): ?>
                <div class="alert alert-danger"><?= $err ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Kode Penerbangan</label>
                    <input type="text" name="flight_code" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Origin</label>
                        <input type="text" name="origin" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Destination</label>
                        <input type="text" name="destination" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Waktu Keberangkatan</label>
                    <input type="datetime-local" name="depart_time" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Tiket</label>
                    <input type="number" name="price" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Jumlah Kursi</label>
                    <input type="number" name="seats" class="form-control" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                    <button class="btn btn-primary" type="submit">Tambah</button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
