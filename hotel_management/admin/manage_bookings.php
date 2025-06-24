<?php
session_start();
require '../db.conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

$adminName = $_SESSION['name'] ?? 'Admin';

$status_filter = $_GET['status'] ?? '';
$name_filter = $_GET['name'] ?? '';

$where = "WHERE u.role = 'user'";
$params = [];

if (!empty($status_filter)) {
    $where .= " AND b.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($name_filter)) {
    $where .= " AND u.full_name LIKE :name";
    $params[':name'] = '%' . $name_filter . '%';
}

$sql = "SELECT 
            b.id, 
            u.full_name AS user_name, 
            rt.type_name AS room_type, 
            b.check_in, 
            b.check_out, 
            b.status, 
            pm.method_name AS payment_method
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        LEFT JOIN payment_methods pm ON b.payment_method_id = pm.id
        $where
        ORDER BY b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar img {
            width: 200px;
            margin-bottom: 25px;
        }

        .sidebar .btn {
            margin-bottom: 10px;
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ccc;
            width: 100%;
        }

        .sidebar .btn:hover {
            background-color: #0077b6;
            color: #fff;
            border-color: #0077b6;
        }

        .main-content {
            margin-left: 270px;
            padding: 30px;
        }

        .dashboard-box {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            vertical-align: middle !important;
        }

        .table thead th {
            background-color: #000;
            color: #fff;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .equal-action-btn {
            width: 90px;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }

            .sidebar .btn {
                width: 45%;
                margin: 5px;
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar text-center">
    <img src="../assets/logo.jpg" alt="Hotel Logo">
    <a href="dashboard.php" class="btn btn-light">Dashboard</a>
    <a href="manage_users.php" class="btn btn-light">Manage Users</a>
    <a href="manage_rooms.php" class="btn btn-light">Manage Rooms</a>
    <a href="manage_bookings.php" class="btn btn-primary">Manage Bookings</a>
    <a href="logout.php" class="btn btn-light">Logout</a>
</div>

<div class="main-content">
    <div class="dashboard-box">
        <h2>Manage Bookings</h2>
        <p class="text-muted">Review and update all bookings</p>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <hr>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name_filter) ?>" class="form-control" placeholder="Enter user name">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="manage_bookings.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <!-- Booking Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Room Type</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $index => $booking): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($booking['user_name']) ?></td>
                                <td><?= htmlspecialchars($booking['room_type']) ?></td>
                                <td><?= htmlspecialchars($booking['check_in']) ?></td>
                                <td><?= htmlspecialchars($booking['check_out']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $booking['status'] === 'confirmed' ? 'success' : 
                                        ($booking['status'] === 'cancelled' ? 'danger' : 
                                        ($booking['status'] === 'pending' ? 'warning' : 'secondary')) ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($booking['payment_method'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <div class="action-buttons">
                                            <a href="update_booking_status.php?id=<?= $booking['id'] ?>&status=confirmed" class="btn btn-success btn-sm equal-action-btn">Confirm</a>
                                            <a href="update_booking_status.php?id=<?= $booking['id'] ?>&status=cancelled" class="btn btn-danger btn-sm equal-action-btn">Decline</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center text-muted">No actions</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No bookings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
