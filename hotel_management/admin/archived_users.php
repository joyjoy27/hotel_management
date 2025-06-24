<?php
session_start();
require '../db.conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM users WHERE is_deleted = 1");
$archivedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Archived Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <h3 class="mb-4">Archived Users</h3>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info text-center"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Created At</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($archivedUsers)): ?>
        <?php foreach ($archivedUsers as $user): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= $user['role'] ?></td>
            <td><?= $user['created_at'] ?? '-' ?></td>
            <td>
              <a href="restore_user.php?id=<?= $user['id'] ?>" class="btn btn-success btn-sm"
                 onclick="return confirm('Restore this user?');">
                Restore
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">No archived users.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <a href="manage_users.php" class="btn btn-secondary mt-3">Back to User Management</a>
</div>

</body>
</html>
