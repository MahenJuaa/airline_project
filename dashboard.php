<?php
require_once 'includes/header.php';
require_once 'config/koneksi.php';
require_once 'includes/functions.php';
require_login();

$isAdmin = ($_SESSION['role'] === 'admin');
$user_id = $_SESSION['user_id'];
$msg = $_GET['msg'] ?? '';

// HANDLE DELETE (via GET) — hanya admin
if ($isAdmin && isset($_GET['delete_flight'])) {
    $del_id = intval($_GET['delete_flight']);
    $dstmt = $mysqli->prepare("DELETE FROM flights WHERE id = ?");
    $dstmt->bind_param("i", $del_id);
    $dstmt->execute();
    header("Location: dashboard.php?msg=flight_deleted");
    exit;
}

// Ambil flights
$flights = $mysqli->query("SELECT * FROM flights ORDER BY depart_time ASC");

// Ambil bookings (admin = semua, user = miliknya)
if ($isAdmin) {
    $orders_sql = "
        SELECT b.*, u.username, f.flight_code, f.origin, f.destination, f.depart_time
        FROM bookings b
        JOIN flights f ON b.flight_id = f.id
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY b.booked_at DESC
    ";
} else {
    $orders_sql = "
        SELECT b.*, f.flight_code, f.origin, f.destination, f.depart_time
        FROM bookings b
        JOIN flights f ON b.flight_id = f.id
        WHERE b.user_id = ?
        ORDER BY b.booked_at DESC
    ";
}

if ($isAdmin) {
    $orders = $mysqli->query($orders_sql);
} else {
    $stmt = $mysqli->prepare($orders_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $orders = $stmt->get_result();
}
?>

<div class="container mt-4">

    <?php if ($msg === 'flight_deleted'): ?>
        <div class="alert alert-success">Penerbangan berhasil dihapus.</div>
    <?php elseif ($msg === 'added'): ?>
        <div class="alert alert-success">Penerbangan berhasil ditambahkan.</div>
    <?php elseif ($msg === 'updated'): ?>
        <div class="alert alert-success">Perubahan penerbangan disimpan.</div>
    <?php elseif ($msg === 'booked'): ?>
        <div class="alert alert-success">Booking berhasil.</div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0"><?= $isAdmin ? "Manajemen Penerbangan (Admin)" : "Pesan Tiket Pesawat" ?></h3>
        <?php if ($isAdmin): ?>
            <a href="add_flight.php" class="btn btn-success">+ Tambah Penerbangan</a>
        <?php endif; ?>
    </div>

    <div class="row g-4 mb-4">
        <?php if ($flights && $flights->num_rows > 0): ?>
            <?php while ($f = $flights->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold"><?= htmlspecialchars($f['flight_code']) ?> — <?= htmlspecialchars($f['origin']) ?></h5>
                            <p class="mb-1"><b>Rute:</b> <?= htmlspecialchars($f['origin']) ?> → <?= htmlspecialchars($f['destination']) ?></p>
                            <p class="mb-1"><b>Berangkat:</b> <?= date("d/m/Y H:i", strtotime($f['depart_time'])) ?></p>
                            <p class="mb-1"><b>Kursi:</b> <?= htmlspecialchars($f['seats']) ?></p>
                            <p class="mb-3"><b>Harga:</b> Rp <?= number_format($f['price'],0,',','.') ?></p>

                            <?php if ($isAdmin): ?>
                                <div class="d-flex gap-2">
                                    <a href="edit_flight.php?id=<?= $f['id'] ?>" class="btn btn-warning w-50">Edit</a>
                                    <a href="dashboard.php?delete_flight=<?= $f['id'] ?>" class="btn btn-danger w-50"
                                       onclick="return confirm('Hapus penerbangan ini?')">Hapus</a>
                                </div>
                            <?php else: ?>
                                <a href="book_tiket.php?id=<?= $f['id'] ?>" class="btn btn-primary w-100">Pesan Tiket</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">Tidak ada penerbangan tersedia.</p>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm p-4">
        <h4 class="mb-3 border-bottom pb-2"><?= $isAdmin ? "Semua Pesanan" : "Pemesanan Saya" ?></h4>

        <?php if (!$orders || $orders->num_rows == 0): ?>
            <p class="text-muted"><?= $isAdmin ? "Belum ada pemesanan." : "Anda belum melakukan pemesanan." ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>Kode</th>
                            <?php if ($isAdmin) echo "<th>Pengguna</th>"; ?>
                            <th>Penerbangan</th>
                            <th>Rute</th>
                            <th>Penumpang</th>
                            <th>KTP/ID</th>
                            <th>Kursi</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($o = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($o['booking_code'] ?? ('BK'.$o['id'])) ?></td>
                                <?php if ($isAdmin): ?>
                                    <td><?= htmlspecialchars($o['username'] ?? '-') ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($o['flight_code']) ?></td>
                                <td><?= htmlspecialchars($o['origin']) ?> → <?= htmlspecialchars($o['destination']) ?></td>
                                <td><?= htmlspecialchars($o['passenger_name']) ?></td>
                                <td><?= htmlspecialchars($o['ktp_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($o['seats_booked']) ?></td>
                                <td>Rp <?= number_format($o['total_price'],0,',','.') ?></td>
                                <td><?= htmlspecialchars($o['status']) ?></td>
                                <td><?= date("d/m/Y H:i", strtotime($o['booked_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
