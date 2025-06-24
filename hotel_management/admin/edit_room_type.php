<?php
require_once '../db.conn.php';

$room_id = $_GET['id'] ?? null;
if (!$room_id) {
    header("Location: manage_rooms.php");
    exit;
}

$error = null;
$success = false;

$stmt = $pdo->query("SELECT id, type_name, description FROM room_types");
$room_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    $error = "Room not found.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_type_id = $_POST['room_type_id'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];
    $max_guests = $_POST['max_guests'];

    $image = $room['image']; 

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "../assets/" . basename($image);
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $error = "Image upload failed.";
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE rooms 
            SET room_type_id = ?, price = ?, availability = ?, max_guests = ?, image = ?, updated_at = NOW()
            WHERE id = ?");
        $stmt->execute([$room_type_id, $price, $availability, $max_guests, $image, $room_id]);
        $success = true;

        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$room_id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Room</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">

<div class="container">
    <h2 class="mb-4">Edit Room</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Room updated successfully!</div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($room): ?>
    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded w-50">
        <div class="mb-3">
            <label class="form-label">Room Type</label>
            <select name="room_type_id" class="form-select" required>
                <?php foreach ($room_types as $type): ?>
                    <option value="<?= $type['id'] ?>"
                        <?= $type['id'] == $room['room_type_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type['type_name']) ?> - <?= htmlspecialchars($type['description']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Price (₱)</label>
            <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($room['price']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Maximum Guests</label>
            <input type="number" name="max_guests" id="max_guests" class="form-control"
                   value="<?= htmlspecialchars($room['max_guests']) ?>" min="1" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Availability</label>
            <select name="availability" class="form-select" required>
                <option value="available" <?= $room['availability'] === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="unavailable" <?= $room['availability'] === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Change Image (optional)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <small class="text-muted">Current image: <?= htmlspecialchars($room['image']) ?></small>
        </div>

        <button type="submit" class="btn btn-primary">Update Room</button>
        <a href="manage_rooms.php" class="btn btn-secondary">Cancel</a>
    </form>
    <?php endif; ?>
</div>

<script>
    const roomTypeSelect = document.querySelector('select[name="room_type_id"]');
    const maxGuestsInput = document.getElementById('max_guests');

    function updateGuestLimit() {
        const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex].text.toLowerCase();

        if (selectedOption.includes('single')) {
            maxGuestsInput.min = 1;
            maxGuestsInput.max = 2;
            if (maxGuestsInput.value > 2) maxGuestsInput.value = 2;
        } else if (selectedOption.includes('double') || selectedOption.includes('deluxe')) {
            maxGuestsInput.min = 2;
            maxGuestsInput.max = 3;
            if (maxGuestsInput.value < 2 || maxGuestsInput.value > 3) maxGuestsInput.value = 2;
        } else if (selectedOption.includes('family') || selectedOption.includes('suite')) {
            maxGuestsInput.min = 3;
            maxGuestsInput.max = 5;
            if (maxGuestsInput.value < 3 || maxGuestsInput.value > 5) maxGuestsInput.value = 3;
        } else {
            maxGuestsInput.min = 1;
            maxGuestsInput.max = 5;
        }
    }

    roomTypeSelect.addEventListener('change', updateGuestLimit);
    window.addEventListener('DOMContentLoaded', updateGuestLimit);
</script>

</body>
</html>
