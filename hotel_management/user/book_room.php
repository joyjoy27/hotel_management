<?php
session_start();
require_once '../db.conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../website/login.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$user_name  = $_SESSION['user_name'] ?? 'Guest';
$user_email = $_SESSION['user_email'] ?? '';

$success = isset($_GET['success']) ? "Booking successful!" : "";
$error = "";

$stmt = $pdo->query("SELECT rooms.id AS room_id, room_types.type_name, room_types.description, rooms.price, rooms.image, rooms.availability
                     FROM rooms
                     JOIN room_types ON rooms.room_type_id = room_types.id
                     WHERE rooms.availability = 'Available'");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

$payment_methods = $pdo->query("SELECT id, method_name FROM payment_methods")->fetchAll();

$bookedDates = [];
$stmt = $pdo->query("SELECT room_id, check_in, check_out FROM bookings WHERE status != 'Cancelled'");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $booking) {
    $bookedDates[] = [
        'room_id'   => $booking['room_id'],
        'check_in'  => $booking['check_in'],
        'check_out' => $booking['check_out']
    ];
}

function isRoomAvailable($pdo, $room_id, $check_in, $check_out) {
    $sql = "SELECT COUNT(*) FROM bookings 
            WHERE room_id = ? AND status != 'Cancelled' AND (
                (check_in <= ? AND check_out > ?) OR
                (check_in < ? AND check_out >= ?) OR
                (check_in >= ? AND check_out <= ?)
            )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$room_id, $check_in, $check_in, $check_out, $check_out, $check_in, $check_out]);
    return $stmt->fetchColumn() == 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['payment_method_id'])) {
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $payment_method_id = $_POST['payment_method_id'];

    if ($room_id && $check_in && $check_out && $payment_method_id) {
        if ($check_in >= $check_out) {
            $error = "Check-out must be after check-in.";
        } elseif (!isRoomAvailable($pdo, $room_id, $check_in, $check_out)) {
            $error = "Selected room is not available for the chosen dates.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO bookings 
                    (user_id, room_id, check_in, check_out, payment_method_id, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
                $stmt->execute([$user_id, $room_id, $check_in, $check_out, $payment_method_id]);
                header("Location: book_room.php?success=1");
                exit();
            } catch (PDOException $e) {
                $error = "Booking failed: " . $e->getMessage();
            }
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a Room - Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        body {
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
        .room-card img {
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="sidebar text-center">
    <img src="../assets/logo.jpg" alt="Hotel Logo">
    <a href="dashboard.php" class="btn w-100">Dashboard</a>
    <a href="book_room.php" class="btn w-100">Book a Room</a>
    <a href="my_bookings.php" class="btn w-100">My Bookings</a>
    <a href="logout.php" class="btn btn-danger w-100">Logout</a>
</div>

<div class="main-content">
    <div class="dashboard-box">
        <h2>Hello, <?= htmlspecialchars($user_name); ?>!</h2>
        <hr>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success; ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <h4>Available Rooms</h4>
        <div class="row">
            <?php foreach ($rooms as $room): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card room-card h-100">
                        <img src="../assets/<?= htmlspecialchars($room['image']); ?>" class="card-img-top">
                        <div class="card-body d-flex flex-column">
                            <h5><?= htmlspecialchars($room['type_name']); ?></h5>
                            <p><?= htmlspecialchars($room['description']); ?></p>
                            <p><strong>Price:</strong> ₱<?= number_format($room['price']); ?> / night</p>
                            <button 
                                class="btn btn-primary mt-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#bookingModal"
                                data-room-id="<?= $room['room_id']; ?>"
                                data-room-type="<?= htmlspecialchars($room['type_name']); ?>"
                                data-price="<?= $room['price']; ?>">
                                Book Now
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="book_room.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Your Stay</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="room_id" id="modalRoomId">
                    <input type="hidden" id="modalRoomPrice">
                    <div class="mb-3">
                        <label>Room Type</label>
                        <input type="text" class="form-control" id="modalRoomType" disabled>
                    </div>
                    <div class="mb-3">
                        <label>Check-In</label>
                        <input type="text" name="check_in" class="form-control" id="checkInDate" required>
                    </div>
                    <div class="mb-3">
                        <label>Check-Out</label>
                        <input type="text" name="check_out" class="form-control" id="checkOutDate" required>
                    </div>
                    <div class="mb-3">
                        <label>Payment Method</label>
                        <select name="payment_method_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach ($payment_methods as $method): ?>
                                <option value="<?= $method['id']; ?>"><?= htmlspecialchars($method['method_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info" id="priceDisplay" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirm Booking</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const bookedDates = <?= json_encode($bookedDates); ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const modal = document.getElementById('bookingModal');
    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const roomId = button.getAttribute('data-room-id');
        const roomType = button.getAttribute('data-room-type');
        const price = parseInt(button.getAttribute('data-price'));

        document.getElementById('modalRoomId').value = roomId;
        document.getElementById('modalRoomType').value = roomType;
        document.getElementById('modalRoomPrice').value = price;
        document.getElementById('priceDisplay').style.display = 'none';

        const checkInInput = document.getElementById('checkInDate');
        const checkOutInput = document.getElementById('checkOutDate');

        const unavailable = [];
        bookedDates.forEach(b => {
            if (b.room_id == roomId) {
                let curr = new Date(b.check_in);
                let end = new Date(b.check_out);
                while (curr <= end) {
                    unavailable.push(curr.toISOString().split('T')[0]);
                    curr.setDate(curr.getDate() + 1);
                }
            }
        });

        flatpickr(checkInInput, {
            dateFormat: "Y-m-d",
            minDate: "today",
            disable: unavailable,
            onChange: function(selectedDates) {
                checkOutInput._flatpickr.set('minDate', selectedDates[0]);
                calculatePrice();
            }
        });

        flatpickr(checkOutInput, {
            dateFormat: "Y-m-d",
            minDate: "today",
            disable: unavailable,
            onChange: calculatePrice
        });
    });

    function calculatePrice() {
        const inDate = document.getElementById('checkInDate').value;
        const outDate = document.getElementById('checkOutDate').value;
        const price = parseInt(document.getElementById('modalRoomPrice').value);
        if (inDate && outDate) {
            const diff = (new Date(outDate) - new Date(inDate)) / (1000 * 60 * 60 * 24);
            if (diff > 0) {
                const total = price * diff;
                const display = document.getElementById('priceDisplay');
                display.innerHTML = `<strong>Total Price: ₱${total.toLocaleString()}</strong>`;
                display.style.display = 'block';
            }
        }
    }
</script>
</body>
</html>
