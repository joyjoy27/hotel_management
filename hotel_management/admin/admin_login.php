<?php
session_start(); 
require '../db.conn.php'; 

$error = ''; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ? AND role = 'admin' AND is_deleted = 0 LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_name'] = $admin['full_name'];
            $_SESSION['user_email'] = $admin['email'];
            $_SESSION['role'] = $admin['role'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in both fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - Hotel Management</title>
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
      text-align: center;
    }
    .left-panel img {
      width: 250px;
      height: auto;
      margin-bottom: 20px;
      border-radius: 8px;
    }
    .left-panel h1 {
    font-family: 'Georgia', serif;
    font-size: 36px;
    font-weight: bold;
    }

    .left-panel p {
    font-family: 'Segoe UI', sans-serif;
   font-size: 18px;
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
    }
    .login-form input[type="text"],
    .login-form input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .password-toggle {
      font-size: 14px;
      margin-bottom: 15px;
    }
    .password-toggle input {
      margin-right: 5px;
    }
    .login-form button {
      width: 100%;
      padding: 12px;
      background-color: black;
      border: none;
      color: white;
      font-weight: bold;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .login-form button:hover {
      background-color: #0077b6;
    }
    .login-form p {
      margin-top: 10px;
      font-size: 14px;
      text-align: center;
    }
    .error {
      color: red;
      margin-bottom: 10px;
      text-align: center;
    }
    @media (max-width: 768px) {
      body { flex-direction: column; }
      .left-panel, .right-panel {
        flex: none;
        width: 100%;
        height: 50%;
      }
    }
  </style>
</head>
<body>
  <div class="left-panel">
    <img src="../assets/logo.jpg" alt="Hotel Logo">
    <h1>Admin Access Panel</h1>
    <p>Welcome, Administrator</p>
  </div>

  <div class="right-panel">
    <form method="POST" class="login-form" action="admin_login.php">
      <h2>Admin Login</h2>
      <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
      <input type="text" name="email" placeholder="Admin Email" required>
      <input type="password" name="password" id="password" placeholder="Password" required>

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
