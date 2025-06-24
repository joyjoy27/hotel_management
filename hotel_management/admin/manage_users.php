<?php
session_start();
require '../db.conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

$adminName = $_SESSION['name'] ?? 'Admin';

$roleFilter = $_GET['role'] ?? '';
$searchName = $_GET['search'] ?? '';

$query = "SELECT * FROM users WHERE is_deleted = 0";
$params = [];

if ($roleFilter) {
    $query .= " AND role = ?";
    $params[] = $roleFilter;
}

if ($searchName) {
    $query .= " AND full_name LIKE ?";
    $params[] = "%" . $searchName . "%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - Admin</title>
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
      top: 0; left: 0;
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
  <a href="dashboard.php" class="btn btn-light w-100">Dashboard</a>
  <a href="manage_users.php" class="btn btn-light w-100">Manage Users</a>
  <a href="manage_rooms.php" class="btn btn-light w-100">Manage Rooms</a>
  <a href="manage_bookings.php" class="btn btn-light w-100">Manage Bookings</a>
  <a href="logout.php" class="btn btn-light w-100">Logout</a>
</div>

<div class="main-content">
  <div class="dashboard-box">
    <h3 class="mb-4">Manage Users</h3>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success text-center"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <form method="GET" class="mb-3 d-flex align-items-center gap-2 flex-wrap">
      <label for="role" class="form-label mb-0">Role:</label>
      <select name="role" id="role" class="form-select w-auto">
        <option value="">All</option>
        <option value="admin" <?= isset($_GET['role']) && $_GET['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="user" <?= isset($_GET['role']) && $_GET['role'] === 'user' ? 'selected' : '' ?>>User</option>
      </select>

      <input type="text" name="search" class="form-control w-auto" placeholder="Search name"
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="manage_users.php" class="btn btn-secondary">Reset</a>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
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
          <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['full_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role'] ?></td>
                <td><?= $user['created_at'] ?? '-' ?></td>
                <td>
                  <?php if ($user['role'] !== 'admin'): ?>
                    <a href="soft_delete_user.php?id=<?= $user['id'] ?>" 
                       class="btn btn-sm btn-warning"
                       onclick="return confirm('Archive this user?');">
                      Archive
                    </a>
                  <?php else: ?>
                    <span class="text-muted">N/A</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center">No users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
