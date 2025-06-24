<?php
session_start();
require_once '../db.conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../website/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Guest';

$cancel_msg = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$stmt = $pdo->prepare("
    SELECT 
        b.id, b.check_in, b.check_out, b.status, 
        pm.method_name AS payment_method, 
        r.id AS room_no,
        rt.type_name AS room_type
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    LEFT JOIN payment_methods pm ON b.payment_method_id = pm.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings - Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background-color: #ffffff; padding: 20px; box-shadow: 2px 0 12px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; align-items: center; }
        .sidebar img { width: 200px; margin-bottom: 25px; }
        .sidebar .btn { margin-bottom: 10px; background-color: #f8f9fa; color: #333; border: 1px solid #ccc; }
        .sidebar .btn:hover { background-color: #0077b6; color: #fff; border-color: #0077b6; }
        .main-content { margin-left: 270px; padding: 30px; }
        .dashboard-box { background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1); }
        @media (max-width: 768px) {
            .sidebar { position: relative; width: 100%; height: auto; flex-direction: row; flex-wrap: wrap; justify-content: center; }
            .sidebar .btn { width: 45%; margin: 5px; }
            .main-content { margin-left: 0; padding: 15px; }
        }
    </style>
</head>
<body>
<div class="sidebar text-center">
    <img src="../assets/logo.jpg" alt="Hotel Logo">
    <a href="dashboard.php" class="btn btn-light w-100">Dashboard</a>
    <a href="book_room.php" class="btn btn-light w-100">Book a Room</a>
    <a href="my_bookings.php" class="btn btn-light w-100">My Bookings</a>
    <a href="logout.php" class="btn btn-light w-100">Logout</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <div class="dashboard-box">
            <h2>Hello, <?= htmlspecialchars($user_name); ?>!</h2>
            <hr>

            <?php if (!empty($cancel_msg)): ?>
                <div class="alert alert-info"><?= htmlspecialchars($cancel_msg); ?></div>
            <?php endif; ?>

            <h4 class="mb-4">Your Bookings</h4>
            <?php if (empty($bookings)): ?>
                <p>You have no bookings yet. <a href="book_room.php">Book a room now</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Booking ID</th>
                                <th>Room Type</th>
                                <th>Room #</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['id']); ?></td>
                                <td><?= htmlspecialchars($b['room_type']); ?></td>
                                <td><?= htmlspecialchars($b['room_no']); ?></td>
                                <td><?= htmlspecialchars($b['check_in']); ?></td>
                                <td><?= htmlspecialchars($b['check_out']); ?></td>
                                <td><?= htmlspecialchars($b['payment_method'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php
                                        $status_raw = htmlspecialchars($b['status']);
                                        $status = ucfirst($status_raw);
                                        switch (strtolower($status_raw)) {
                                            case 'confirmed': $badge = 'success'; break;
                                            case 'pending': $badge = 'warning text-dark'; break;
                                            case 'cancelled': $badge = 'danger'; break;
                                            default: $badge = 'secondary';
                                        }
                                    ?>
                                    <span class="badge bg-<?= $badge; ?>"><?= $status; ?></span>
                                </td>
                                <td>
                                    <?php if (!in_array(strtolower($status_raw), ['cancelled'])): ?>
                                    <form method="POST" action="cancel_booking.php" onsubmit="return confirm('Cancel this booking?');">
                                        <input type="hidden" name="booking_id" value="<?= $b['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                    </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>Unavailable</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
