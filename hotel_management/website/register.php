<?php
session_start();
require '../db.conn.php';

$error = "";
$full_name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email     = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password  = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm   = isset($_POST['confirm']) ? $_POST['confirm'] : '';

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "This email is already registered. <a href='login.php'>Click here to login</a>.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");

            if ($stmt->execute([$full_name, $email, $hashedPassword])) {
                $_SESSION['success'] = "Registration successful! You can now log in.";
                header("Location: register.php");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Hotel Service</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f0f0;
      display: flex;
      height: 100vh;
    }
    .left-panel {
      flex: 1;
      background: url('../assets/background.jpg') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: white;
      padding: 20px;
    }
    .left-panel img {
      width: 250px;
      height: auto;
      margin-bottom: 20px;
      border-radius: 8px;
    }
    .right-panel {
      flex: 1;
      background-color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
    }
    .register-form {
      width: 100%;
      max-width: 350px;
    }
    .register-form h2 {
      margin-bottom: 20px;
      color: #333;
      text-align: center;
    }
    .register-form input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    .register-form button {
      width: 100%;
      padding: 10px;
      background-color: rgb(0, 0, 0);
      border: none;
      color: white;
      font-size: 16px;
      cursor: pointer;
      border-radius: 6px;
    }
    .register-form button:hover {
      background-color: #0077b6;
    }
    .register-form p {
      text-align: center;
      margin-top: 10px;
    }
    .register-form a {
      text-decoration: none;
      color: #0077b6;
    }
    .register-form a:hover {
      text-decoration: underline;
    }
    .error {
      color: red;
      background: #fdd;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 10px;
      text-align: center;
      border: 1px solid #f5c2c2;
    }
    .success {
      color: green;
      background: #d4edda;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 10px;
      text-align: center;
      border: 1px solid #c3e6cb;
    }
  </style>
</head>
<body>

<div class="left-panel">
  <img src="../assets/logo.jpg" alt="Hotel Logo">
  <h1>Welcome to Our Hotel</h1>
  <p>Join us and experience comfort at its finest.</p>
</div>

<div class="right-panel">
  <form method="POST" class="register-form" action="register.php">
    <h2>Create Account</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
      <div class="success"><?= $_SESSION['success'] ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <input type="text" name="full_name" placeholder="Full Name" value="<?= htmlspecialchars($full_name) ?>" required>
    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="confirm" placeholder="Confirm Password" required>

    <button type="submit">Register</button>

    <p>Already have an account? <a href="login.php">Login here</a>.</p>
  </form>
</div>

</body>
</html>
