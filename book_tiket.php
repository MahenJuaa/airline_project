<?php
require_once 'includes/header.php';
require_once 'config/koneksi.php';
require_once 'includes/functions.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $mysqli->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$flight = $stmt->get_result()->fetch_assoc();

if (!$flight) {
    header("Location: dashboard.php?msg=notfound");
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_name = trim($_POST['passenger_name'] ?? '');
    $ktp_number = trim($_POST['ktp_number'] ?? '');
    $seats = 1; // auto

    if ($passenger_name === '' || $ktp_number === '') {
        $err = 'Nama dan Nomor ID wajib diisi.';
    } elseif ($seats > $flight['seats']) {
        $err = 'Kursi tidak cukup.';
    } else {
        $total = $flight['price'];
        $booking_code = 'BK' . date('YmdHis') . rand(10,99);
        $status = 'Confirmed';
        $user_id = $_SESSION['user_id'];

        $mysqli->begin_transaction();

        $u = $mysqli->prepare("UPDATE flights SET seats = seats - ? WHERE id = ? AND seats >= ?");
        $u->bind_param("iii", $seats, $id, $seats);

        if ($u->execute() && $u->affected_rows > 0) {
            $ins = $mysqli->prepare("
                INSERT INTO bookings
                (booking_code, user_id, flight_id, passenger_name, ktp_number, seats_booked, total_price, status, booked_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $ins->bind_param("siissids", $booking_code, $user_id, $id, $passenger_name, $ktp_number, $seats, $total, $status);

            if ($ins->execute()) {
                $mysqli->commit();
                header("Location: dashboard.php?msg=booked");
                exit;
            } else {
                $mysqli->rollback();
                $err = 'Gagal menyimpan booking: ' . $mysqli->error;
            }
        } else {
            $mysqli->rollback();
            $err = 'Tidak cukup kursi tersedia.';
        }
    }
}
?>

<div class="container mt-4">
    <div class="card shadow-sm mx-auto" style="max-width:720px;">
        <div class="card-body">
            <h4>Pesan Tiket — <?= htmlspecialchars($flight['flight_code']) ?></h4>

            <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

            <p><b>Rute:</b> <?= htmlspecialchars($flight['origin']) ?> → <?= htmlspecialchars($flight['destination']) ?></p>
            <p><b>Berangkat:</b> <?= date("d/m/Y H:i", strtotime($flight['depart_time'])) ?></p>
            <p><b>Harga:</b> Rp <?= number_format($flight['price'],0,',','.') ?></p>
            <p><b>Kursi Tersedia:</b> <?= htmlspecialchars($flight['seats']) ?></p>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nama Penumpang</label>
                    <input name="passenger_name" class="form-control" required value="<?= htmlspecialchars($_POST['passenger_name'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor ID (KTP / Paspor)</label>
                    <input name="ktp_number" class="form-control" required value="<?= htmlspecialchars($_POST['ktp_number'] ?? '') ?>">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                    <button class="btn btn-primary">Konfirmasi Pesan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
