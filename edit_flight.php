<?php
require_once 'includes/header.php';
require_once 'config/koneksi.php';
require_once 'includes/functions.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
$f = $mysqli->query("SELECT * FROM flights WHERE id = $id")->fetch_assoc();
if (!$f) {
    header("Location: dashboard.php?msg=notfound");
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flight_code = trim($_POST['flight_code']);
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $depart_time = trim($_POST['depart_time']);
    $price = floatval($_POST['price']);
    $seats = intval($_POST['seats']);

    if ($flight_code === '' || $origin === '' || $destination === '' || $depart_time === '') {
        $err = 'Semua field wajib diisi.';
    } else {
        $up = $mysqli->prepare("UPDATE flights SET flight_code=?, origin=?, destination=?, depart_time=?, price=?, seats=? WHERE id=?");
        $up->bind_param("ssssdii", $flight_code, $origin, $destination, $depart_time, $price, $seats, $id);
        if ($up->execute()) {
            header("Location: dashboard.php?msg=updated");
            exit;
        } else {
            $err = "Gagal update: " . $mysqli->error;
        }
    }
}
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>Edit Penerbangan</h4>

            <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Kode Penerbangan</label>
                    <input name="flight_code" class="form-control" required value="<?= htmlspecialchars($f['flight_code']) ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Origin</label>
                        <input name="origin" class="form-control" required value="<?= htmlspecialchars($f['origin']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Destination</label>
                        <input name="destination" class="form-control" required value="<?= htmlspecialchars($f['destination']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Waktu Keberangkatan</label>
                    <input type="datetime-local" name="depart_time" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime($f['depart_time'])) ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" name="price" class="form-control" required value="<?= htmlspecialchars($f['price']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kursi</label>
                        <input type="number" name="seats" class="form-control" required value="<?= htmlspecialchars($f['seats']) ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
