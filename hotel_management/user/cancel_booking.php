<?php
session_start();
require_once '../db.conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../website/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking && strtolower($booking['status']) !== 'cancelled') {
        $update = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $update->execute([$booking_id]);

        $_SESSION['message'] = "Booking #{$booking_id} has been successfully cancelled.";
    } else {
        $_SESSION['message'] = "Invalid booking or already cancelled.";
    }
} else {
    $_SESSION['message'] = "Invalid request.";
}

header("Location: my_bookings.php");
exit();
