<?php
require_once '../db.conn.php';

if (isset($_POST['room_id'], $_POST['availability'])) {
    $roomId = $_POST['room_id'];
    $availability = $_POST['availability'];

    $stmt = $pdo->prepare("UPDATE rooms SET availability = ? WHERE id = ?");
    $stmt->execute([$availability, $roomId]);
}

header("Location: manage_rooms.php?msg=Room availability updated");
exit();
