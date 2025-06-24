<?php
session_start();
require '../db.conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

$stmt = $pdo->query("SELECT id, type_name, description FROM room_types");
$roomTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_type_id = $_POST['room_type_id'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];
    $max_guests = $_POST['max_guests'];
    $image = time() . '_' . $_FILES['image']['name'];

    $target = "../assets/" . basename($image);
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO rooms (room_type_id, price, image, availability, max_guests, created_at) 
                               VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$room_type_id, $price, $image, $availability, $max_guests]);
        $success = true;
    } else {
        $error = "Image upload failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Room - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 30px; background-color: #f8f9fa; }
        .form-container {
            max-width: 600px; margin: auto;
            background: white; padding: 30px; border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="form-container">
    <h3 class="mb-4">Add New Room</h3>

    <?php if ($success): ?>
        <div class="alert alert-success">Room successfully added! Redirecting to Manage Rooms...</div>
        <script>
            setTimeout(() => window.location.href = 'manage_rooms.php', 3000);
        </script>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to add this room?')">
        <div class="mb-3">
            <label for="room_type_id" class="form-label">Room Type</label>
            <select class="form-select" name="room_type_id" id="room_type_id" required onchange="adjustGuestLimits()">
                <option value="">Select a type</option>
                <?php foreach ($roomTypes as $type): ?>
                    <option value="<?= $type['id'] ?>" data-name="<?= strtolower($type['type_name']) ?>">
                        <?= htmlspecialchars($type['type_name']) ?> - <?= htmlspecialchars($type['description']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Price (₱)</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Maximum Guests</label>
            <input type="number" name="max_guests" id="max_guests" class="form-control" min="1" max="5" required>
            <div class="form-text" id="guest-hint">Select room type to see guest limits</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Availability</label>
            <select name="availability" class="form-select" required>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Room Image</label>
            <input type="file" name="image" class="form-control" accept="image/*" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Room</button>
        <a href="manage_rooms.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
function adjustGuestLimits() {
    const select = document.getElementById('room_type_id');
    const selectedOption = select.options[select.selectedIndex];
    const typeName = selectedOption.getAttribute('data-name') || '';
    const guestInput = document.getElementById('max_guests');
    const hint = document.getElementById('guest-hint');

    if (typeName.includes('single')) {
        guestInput.min = 1;
        guestInput.max = 2;
        hint.textContent = "Allowed guests: 1 to 2";
    } else if (typeName.includes('double') || typeName.includes('deluxe')) {
        guestInput.min = 2;
        guestInput.max = 3;
        hint.textContent = "Allowed guests: 2 to 3";
    } else if (typeName.includes('family') || typeName.includes('suite')) {
        guestInput.min = 3;
        guestInput.max = 5;
        hint.textContent = "Allowed guests: 3 to 5";
    } else {
        guestInput.min = 1;
        guestInput.max = 5;
        hint.textContent = "Allowed guests: 1 to 5";
    }

    const val = parseInt(guestInput.value);
    if (val < guestInput.min || val > guestInput.max) {
        guestInput.value = '';
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
