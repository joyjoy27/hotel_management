<?php
session_start();
require '../db.conn.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hotel Website</title>
  <link rel="stylesheet" href="style.css">
  <style>
    html { scroll-behavior: smooth; }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-image: url(../assets/background.jpg);
      background-size: cover;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }
    .main-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #333;
      color: white;
      padding: 10px 30px;
      position: sticky;
      top: 0;
      z-index: 1000;
      flex-wrap: wrap;
    }
    .header-left {
      font-size: 18px;
      font-weight: bold;
    }
    .header-center ul {
      list-style: none;
      display: flex;
      gap: 20px;
      margin: 0;
      padding: 0;
    }
    .header-center a {
      color: white;
      text-decoration: none;
      position: relative;
      padding: 5px 0;
      transition: color 0.3s ease;
    }
    .header-center a::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      height: 3px;
      width: 0;
      background-color: #0077b6;
      transition: width 0.3s ease;
    }
    .header-center a:hover::after {
      width: 100%;
    }
    .header-center a:hover {
      color: white;
    }
    .header-right {
      display: flex;
      gap: 15px;
    }
    .auth-btn {
      color: white;
      text-decoration: none;
      padding: 6px 12px;
      border: 1px solid white;
      border-radius: 4px;
      transition: background-color 0.3s, color 0.3s;
      font-size: 14px;
    }
    .auth-btn:hover {
      background-color: #0077b6;
      color: #333;
    }
    @media (max-width: 768px) {
      .main-header {
        flex-direction: column;
        align-items: flex-start;
      }
      .header-center ul {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
      .header-right {
        margin-top: 10px;
      }
    }
    section {
      padding: 80px 30px;
    }

    .section-container {
      background-color: rgba(0, 0, 0, 0.25);
      padding: 40px;
      border-radius: 12px;
      max-width: 1000px;
      margin: auto;
    }

    #home h1 {
      font-size: 48px;
      margin-bottom: 10px;
      color: #fff;
    }
    #home p {
      font-size: 20px;
      max-width: 800px;
      line-height: 1.8;
      text-align: justify;
      color: #fff;
    }
    .book-now-btn {
      display: inline-block;
      margin-top: 20px;
      background-color:rgb(0, 0, 0);
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }
    .book-now-btn:hover {
      background-color: #0077b6;
    }
    .room-container {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
      padding: 20px;
    }
    .room-card {
      background-color: rgba(0, 0, 0, 0.25);
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      width: 300px;
      padding: 20px;
      text-align: center;
      transition: transform 0.3s;
      position: relative;
      overflow: hidden;
    }
    .room-card:hover {
      transform: translateY(-5px);
    }
    .room-image-box {
      position: relative;
    }
    .room-image-box img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }
    .image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.3);
      border-radius: 8px;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
      font-weight: bold;
      font-size: 16px;
    }
    .price {
      font-size: 18px;
      color: #00B4D8;
      font-weight: bold;
      margin: 10px 0;
    }
    ul {
      list-style-type: none;
      padding: 0;
    }
    ul li {
      margin: 5px 0;
      color: #ddd;
    }
    .room-card h3 {
      font-weight: 600;
      color: #fff;
    }
    .room-card p {
      color: #ddd;
    }

    .contact-section {
      padding: 60px 20px;
      text-align: center;
    }
    .contact-heading {
      font-size: 36px;
      font-weight: 700;
      margin-bottom: 40px;
      color: #fff;
    }
    .contact-content {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: flex-start;
      gap: 40px;
      max-width: 1000px;
      margin: 0 auto;
    }
    .contact-info,
    .contact-form-container {
      flex: 1 1 300px;
      text-align: left;
    }
    .contact-info h3,
    .contact-form-container h3 {
      font-size: 30px;
      margin-bottom: 15px;
      color: #fff;
    }
    .contact-info p,
    .contact-info a {
      font-size: 20px;
      margin: 8px 0;
      color: #fff;
    }
    .contact-form {
      display: flex;
      flex-direction: column;
    }
    .contact-form input,
    .contact-form textarea {
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
      color: #000;
      background-color: #fff;
    }
    .contact-form textarea {
      resize: vertical;
      min-height: 120px;
    }
    .contact-form button {
      padding: 12px;
      background-color: rgb(0, 0, 0);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .contact-form button:hover {
      background-color: #00B4D8;
    }
    @media (max-width: 768px) {
      .contact-content {
        flex-direction: column;
        align-items: center;
      }
      .contact-info,
      .contact-form-container {
        width: 100%;
        text-align: center;
      }
      .contact-form input,
      .contact-form textarea {
        text-align: left;
      }
    }

    footer {
      background-color: #333;
      color: #fff;
      text-align: center;
      padding: 20px;
      margin-top: 40px;
    }
    footer p {
      margin: 5px;
      font-size: 14px;
    }
  </style>
</head>
<body>

<header class="main-header">
  <div class="header-left">Hotel Service Management System</div>
  <nav class="header-center">
    <ul>
      <li><a href="#home">Home</a></li>
      <li><a href="#room">Room</a></li>
      <li><a href="#contact">Contact Us</a></li>
    </ul>
  </nav>
  <div class="header-right">
    <a href="login.php" class="auth-btn">Login</a>
    <a href="register.php" class="auth-btn">Sign Up</a>
  </div>
</header>

<section id="home">
  <div class="section-container">
    <h1>Welcome to Our Hotel</h1>
    <p>Relax. Refresh. Recharge.</p>
    <p>At Hotel Service Management System, we offer a luxurious and peaceful stay experience with world-class amenities. Whether you're traveling for business or leisure, our well-furnished rooms, dedicated service, and relaxing atmosphere ensure your comfort is our priority.</p>
    <a href="login.php" class="book-now-btn">Book Now</a>
  </div>
</section>

<section id="room">
  <h2 style="text-align:center; font-weight: bold; color:#fff;">Discover Our Featured Rooms</h2>
  <div class="room-container">
    <div class="room-card">
      <div class="room-image-box">
        <img src="../assets/presidential.jpg" alt="Deluxe Room">
      </div>
      <h3>Deluxe Room</h3>
      <p class="price">₱4,200 / night</p>
      <p>Spacious room with a king-size bed, ensuite bathroom, and balcony view.</p>
      <ul>
        <li>Wi-Fi</li>
        <li>Air Conditioning</li>
        <li>TV & Mini Bar</li>
      </ul>
      <a href="login.php" class="book-now-btn">Book Now</a>
    </div>

    <div class="room-card">
      <div class="room-image-box">
        <img src="../assets/family suite.jpg" alt="Family Suite">
      </div>
      <h3>Family Suite</h3>
      <p class="price">₱6,500 / night</p>
      <p>Perfect for families, includes two beds, dining area, and kids' space.</p>
      <ul>
        <li>2 Queen Beds</li>
        <li>Free Breakfast</li>
        <li>Private Lounge</li>
      </ul>
      <a href="login.php" class="book-now-btn">Book Now</a>
    </div>

    <div class="room-card">
      <div class="room-image-box">
        <img src="../assets/executive.jpg" alt="Executive Room">
      </div>
      <h3>Executive Room</h3>
      <p class="price">₱5,000 / night</p>
      <p>Ideal for business travelers, with desk, high-speed internet, and room service.</p>
      <ul>
        <li>Work Desk</li>
        <li>Room Service</li>
        <li>City View</li>
      </ul>
      <a href="login.php" class="book-now-btn">Book Now</a>
    </div>
  </div>
</section>

<section id="contact" class="contact-section">
  <div class="section-container">
    <h1 class="contact-heading">Contact Us</h1>
    <div class="contact-content">
      <div class="contact-info">
        <h3>Get in Touch</h3>
        <p><strong>Email:</strong> <a href="mailto:info@hotelservices.com">paulaeuniceee@gmail.com</a></p>
        <p><strong>Phone:</strong> <a href="tel:+639123456789">09854222087</a></p>
      </div>
      <div class="contact-form-container">
        <h3>Send Us a Message</h3>
        <form action="#" method="post" class="contact-form">
          <input type="text" name="name" placeholder="Your Name" required>
          <input type="email" name="email" placeholder="Your Email" required>
          <textarea name="message" placeholder="Your Message" required></textarea>
          <button type="submit">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</section>

<footer>
  <p>&copy; <?php echo date("Y"); ?> Hotel Service Management System. All rights reserved.</p>
</footer>

</body>
</html>
