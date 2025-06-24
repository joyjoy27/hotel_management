<?php
require '../db.conn.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    $stmt = $pdo->prepare("UPDATE users SET is_deleted = 1 WHERE id = ?");
    $stmt->execute([$userId]);

    header("Location: manage_users.php?msg=User archived successfully");
    exit;
} else {
    header("Location: manage_users.php?msg=Invalid user ID");
    exit;
}
