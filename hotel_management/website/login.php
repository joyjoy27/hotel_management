<?php
session_start();
require '../db.conn.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
                exit();
            } elseif ($user['role'] === 'user') {
                header("Location: ../user/dashboard.php");
                exit();
            } else {
                $error = "Unknown user role.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Hotel Service</title>
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
    text-align: center; 
    color: white;
    padding: 40px 20px;
    }
    .left-panel img {
      width: 250px;
      height: auto;
      margin-bottom: 20px;
      border-radius: 8px;
    }
    .left-panel h1 {
    font-size: 36px;
    margin: 20px 0 10px;
    line-height: 1.3;
    font-weight: bold;
    font-family: 'Georgia', serif; 
    }

    .left-panel p {
    font-size: 18px;
    max-width: 300px;
    line-height: 1.6;
    margin: 0 auto;
    font-family: 'Segoe UI', sans-serif;
    }

    .right-panel {
      flex: 1;
      background-color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
    }
    .login-form {
      width: 100%;
      max-width: 350px;
    }
    .login-form h2 {
      margin-bottom: 20px;
      color: #333;
      text-align: center;
    }
    .login-form input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    .login-form button {
      width: 100%;
      padding: 10px;
      background-color: rgb(0, 0, 0);
      border: none;
      color: white;
      font-size: 16px;
      cursor: pointer;
      border-radius: 6px;
    }
    .login-form button:hover {
      background-color: #0077b6;
    }
    .login-form p {
      text-align: center;
      margin-top: 10px;
    }
    .login-form a {
      text-decoration: none;
      color: #0077b6;
    }
    .login-form a:hover {
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
  </style>
</head>
<body>

<div class="left-panel">
  <img src="../assets/logo.jpg" alt="Hotel Logo">
  <h1>Welcome Back to <br> Timeless Stays </h1>
  <p>Log in to manage your bookings and enjoy your stay.</p>
</div>

<div class="right-panel">
  <form method="POST" class="login-form" action="login.php">
    <h2>Login</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    
    <button type="submit">Login</button>

    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
  </form>
</div>

</body>
</html>
