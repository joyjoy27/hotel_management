<?php
session_start();
require '../db.conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/admin_login.php");
    exit;
}

$adminName = $_SESSION['name'] ?? 'Admin';

$userCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$bookingCount = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Hotel Management</title>
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
        .stat {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        .stat:hover {
            background-color: #dee2e6;
            cursor: pointer;
            box-shadow: 0px 4px 16px rgba(0, 0, 0, 0.2);
        }
        .stat h3 {
            margin-bottom: 10px;
            font-size: 28px;
        }
        .stat p {
            margin: 0;
            color: #666;
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
    <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
    <a href="manage_users.php" class="btn btn-light">Manage Users</a>
    <a href="manage_rooms.php" class="btn btn-light">Manage Rooms</a>
    <a href="manage_bookings.php" class="btn btn-light">Manage Bookings</a>
    <a href="logout.php" class="btn btn-light">Logout</a>
</div>

<div class="main-content">
    <div class="dashboard-box">
        <h2>Welcome, <?= htmlspecialchars($adminName) ?>!</h2>
        <p class="text-muted">This is your admin dashboard.</p>
        <hr>

        <div class="row">
            <div class="col-md-6">
                <a href="manage_users.php" style="text-decoration: none; color: inherit;">
                    <div class="stat">
                        <h3><?= $userCount ?></h3>
                        <p>Total Users</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="manage_bookings.php" style="text-decoration: none; color: inherit;">
                    <div class="stat">
                        <h3><?= $bookingCount ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
