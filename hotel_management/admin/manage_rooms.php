<?php
session_start();
require '../db.conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $roomId = $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);

    header("Location: manage_rooms.php?msg=Room deleted successfully");
    exit;
}

$typeFilter = $_GET['type'] ?? '';
$availabilityFilter = $_GET['availability'] ?? '';

$query = "SELECT rooms.*, room_types.type_name 
          FROM rooms 
          JOIN room_types ON rooms.room_type_id = room_types.id 
          WHERE 1=1";

$params = [];

if (!empty($typeFilter)) {
    $query .= " AND room_types.type_name = ?";
    $params[] = $typeFilter;
}

if (!empty($availabilityFilter)) {
    $query .= " AND rooms.availability = ?";
    $params[] = $availabilityFilter;
}

$query .= " ORDER BY rooms.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

$typeStmt = $pdo->query("SELECT DISTINCT type_name FROM room_types");
$roomTypes = $typeStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Rooms - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background-color: #f5f5f5; }
    .sidebar {
      height: 100vh; width: 250px; position: fixed; top: 0; left: 0;
      background-color: #fff; padding: 20px;
      box-shadow: 2px 0 12px rgba(0, 0, 0, 0.1);
      display: flex; flex-direction: column; align-items: center;
    }
    .sidebar img { width: 200px; margin-bottom: 25px; }
    .sidebar .btn {
      margin-bottom: 10px; background-color: #f8f9fa; color: #333; border: 1px solid #ccc;
    }
    .sidebar .btn:hover {
      background-color: #0077b6; color: #fff; border-color: #0077b6;
    }
    .main-content { margin-left: 270px; padding: 30px; }
    .dashboard-box {
      background: #fff; border-radius: 10px; padding: 30px;
      box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
    }
    table img { border-radius: 5px; }
    @media (max-width: 768px) {
      .sidebar { position: relative; width: 100%; height: auto; flex-direction: row; flex-wrap: wrap; justify-content: center; }
      .sidebar .btn { width: 45%; margin: 5px; }
      .main-content { margin-left: 0; padding: 15px; }
    }
  </style>
</head>
<body>

<div class="sidebar text-center">
  <img src="../assets/logo.jpg" alt="Hotel Logo" />
  <a href="dashboard.php" class="btn btn-light w-100">Dashboard</a>
  <a href="manage_users.php" class="btn btn-light w-100">Manage Users</a>
  <a href="manage_rooms.php" class="btn btn-light w-100">Manage Rooms</a>
  <a href="manage_bookings.php" class="btn btn-light w-100">Manage Bookings</a>
  <a href="logout.php" class="btn btn-light w-100">Logout</a>
</div>

<div class="main-content">
  <div class="dashboard-box">
    <h2 class="mb-4">Manage Rooms</h2>
    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <a href="add_room.php" class="btn btn-success mb-3">Add New Room</a>
    <form method="GET" class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">Room Type</label>
        <select name="type" class="form-select">
          <option value="">All Types</option>
          <?php foreach ($roomTypes as $type): ?>
            <option value="<?= htmlspecialchars($type) ?>" <?= $type === $typeFilter ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Availability</label>
        <select name="availability" class="form-select">
          <option value="">All</option>
          <option value="available" <?= $availabilityFilter === 'available' ? 'selected' : '' ?>>Available</option>
          <option value="unavailable" <?= $availabilityFilter === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
        </select>
      </div>

      <div class="col-md-4 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary w-50">Apply Filters</button>
        <a href="manage_rooms.php" class="btn btn-secondary w-50">Reset</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Room Type</th>
            <th>Price</th>
            <th>Availability</th>
            <th>Image</th>
            <th>Created At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $count = 1; ?>
          <?php foreach ($rooms as $room): ?>
            <tr>
              <td><?= $count++ ?></td>
              <td><?= htmlspecialchars($room['type_name']) ?></td>
              <td>₱<?= number_format($room['price'], 2) ?></td>
              <td><?= htmlspecialchars($room['availability']) ?></td>
              <td><img src="../assets/<?= htmlspecialchars($room['image']) ?>" width="100" alt="Room Image" /></td>
              <td><?= htmlspecialchars($room['created_at']) ?></td>
              <td>
                <div class="d-flex gap-2">
                  <a href="edit_room.php?id=<?= $room['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                  <a href="?delete=<?= $room['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this room?')">Delete</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($rooms)): ?>
            <tr><td colspan="7" class="text-center">No rooms found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
