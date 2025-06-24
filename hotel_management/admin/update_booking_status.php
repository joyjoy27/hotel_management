<?php
session_start();
require '../db.conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['id'], $_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];

    $allowed = ['confirmed', 'cancelled'];

    if (in_array($status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $_SESSION['success'] = "Booking status updated to '" . ucfirst($status) . "'.";
    } else {
        $_SESSION['error'] = "Invalid status value.";
    }
} else {
    $_SESSION['error'] = "Missing booking ID or status.";
}

header("Location: manage_bookings.php");
exit;
